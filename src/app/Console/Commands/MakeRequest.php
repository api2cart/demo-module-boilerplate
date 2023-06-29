<?php

namespace App\Console\Commands;

use Api2Cart\Client\Model\ModelInterface;
use App\Models\User;
use App\Services\Api2Cart;
use Carbon\Carbon;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\Utils as GuzzleHttpPromiseUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class MakeRequest extends Command
{
  private $_api2Cart;

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'sendRequestToA2C
                         {method : Method name}
                         {parameters : base64encoded parameters}
                         {resultEntity : Result entity in response}
                         {resultEntityCount : Count of entity in response}
                         {--subEntities=no : Get sub entities}
                         {--subEntityId=id : SubEntity ID}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Send request to Api2Cart';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct(Api2Cart $api2Cart)
  {
    $this->_api2Cart = $api2Cart;
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return void
   * @throws \Throwable
   */
  public function handle()
  {
    $userId = $_SERVER['USER_ID'];

    $user = User::find($userId);

    if ($user) {
      $logs = collect([]);
      $entities = collect([]);
      Auth::login($user);
      $this->_api2Cart->setApiKey($user->api2cart_key);
      $resultEntity = $this->argument('resultEntity');
      $resultEntityCount = $this->argument('resultEntityCount');
      $method = $this->argument('method');

      $parameters = json_decode(base64_decode($this->argument('parameters')), true);
      $cartId = $parameters['cart_id'] ?? '';
      unset($parameters['cart_id']);
      $subEntities = strtolower($this->option('subEntities'));

      $result = $this->_api2Cart->{$method}(...$parameters);

      $entities = $this->_getEntities($result, $resultEntityCount, $resultEntity, $entities, $cartId);

      if (isset($result['pagination']['next']) && strlen($result['pagination']['next'])) {
        while (isset($result['pagination']['next']) && strlen($result['pagination']['next'])) {
          $result = $this->_api2Cart->{$method . 'Page'}(null, $result['pagination']['next']);
          $entities = $this->_getEntities($result, $resultEntityCount, $resultEntity, $entities, $cartId);
        }
      }

      if ($entities->count()) {
        $entities = $entities->keyBy('id');
      }

      if ($subEntities !== 'no') {
        $subEntityId = strtolower($this->option('subEntityId'));
        $entitiesIds = $entities->pluck($subEntityId)->all();

        $maxConcurrent = 5;

        /**
         * @var Promise[] $promises
         */
        $promises = [];
        $subEntitiesResults = [];

        foreach ($entitiesIds as $entityId) {
          if (count($promises) >= $maxConcurrent) {
            GuzzleHttpPromiseUtils::unwrap($promises);

            foreach ($promises as $entityId => $promise) {
              if ($promise->getState() === Promise::FULFILLED) {

                /**
                 * @var ModelInterface $result
                 */
                $result = $promise->wait()[0] ?? [];

                if ($result instanceof ModelInterface && $result->getReturnCode() === 10) {
                  list($promise, $callback) = $this->_api2Cart->{$subEntities}($entityId);
                  $subEntitiesResults[$entityId]['callback'] = $callback;
                  $promises[$entityId] = $promise;
                  break 2;
                } else {
                  $subEntitiesResults[$entityId]['result'] = $result;
                }
              }
            }

            $promises = [];
            usleep(1000000);
          }

          list($promise, $callback) = $this->_api2Cart->{$subEntities}($entityId);
          $subEntitiesResults[$entityId]['callback'] = $callback;
          $promises[$entityId] = $promise;
        }

        GuzzleHttpPromiseUtils::unwrap($promises);

        foreach ($promises as $entityId => $promise) {
          $subEntitiesResults[$entityId]['result'] = $promise->wait()[0] ?? [];
        }

        $entities = $entities->transform(function ($item) use ($subEntitiesResults, $subEntityId) {
          if (isset($subEntitiesResults[$item[$subEntityId]]['result'])
            && $subEntitiesResults[$item[$subEntityId]]['result'] instanceof ModelInterface
          ) {
            $item['sub_entities'] = $subEntitiesResults[$item[$subEntityId]]['callback']($subEntitiesResults[$item[$subEntityId]]['result']);
          }

          return $item;
        });
      }

      foreach ($this->_api2Cart->getLog()->all() as $item) {
        $logs->push($item);
      }

      if (isset($entities->first()->create_at)) {
        $entities = $entities->sortBy('create_at.value');
      } else {
        $entities = $entities->sortBy('id');
      }

      $data = collect([
        'result' => $entities,
        'logs' => $this->_api2Cart->getLog()->all()
      ]);

      $this->getOutput()->write($data->toJson());
    } else {
      $this->error('User not found');
    }
  }

  /**
   * @param array      $result            Result
   * @param string     $resultEntityCount Result entity count field name
   * @param string     $resultEntity      Result entity field name
   * @param Collection $entities          Entities
   * @param string     $cartId            Cart ID
   *
   * @return Collection
   */
  protected function _getEntities(
    array $result, $resultEntityCount, $resultEntity, Collection $entities, string $cartId
  ): Collection
  {
    $resEntities = (!empty($result['result'][$resultEntityCount]))
      ? collect($result['result'][$resultEntity])
      : collect([]);

    if ($resEntities->count()) {
      foreach ($resEntities as $item) {
        $newItem = $item;
        $newItem['create_at']['value'] = Carbon::parse($item['create_at']['value'])->setTimezone('UTC')->format("Y-m-d\TH:i:sO");
        $newItem['cart_id'] = $cartId;
        $entities->push($newItem);
      }
    }

    return $entities;
  }
}