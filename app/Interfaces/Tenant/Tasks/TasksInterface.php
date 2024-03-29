<?php

namespace App\Interfaces\Tenant\Tasks;

use Livewire\Request;
use App\Models\Tenant\Tasks;
use App\Models\Tenant\Pedidos;
use App\Models\Tenant\SerieNumbers;
use App\Models\Tenant\TasksReports;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\Tenant\Tasks\TasksFormRequest;
use App\Models\Tenant\Intervencoes;

interface TasksInterface
{
    public function add(TasksFormRequest $request): Tasks;

    public function dispatchTask(Tasks $task): TasksReports;

    public function getTasks($perPage);

    public function getTaskSearch($searchString,$perPage): LengthAwarePaginator;

    public function getTask($task): Pedidos;

    public function getTaskById($taskId): Pedidos;

    public function updateTask(Tasks $task, object $values): bool;

    public function createPedido(object $values): Pedidos;

    public function updatePedido(object $values): int;

    /**FILTRO */

    public function getTasksFilter(string $searchString,int $tech,int $client,int $typeReport,int $work,string $ordenation,string $dateBegin,string $dateEnd,$perPage): LengthAwarePaginator;

    /**FIM FILTRO */

    /** Search Serie Number */

    public function searchSerialNumber($serialNumber): LengthAwarePaginator;

    /********* */

    /** INTERVENCOES ***/

    public function getIntervencoes($perPage);

    public function getIntervencaoSearch($searchString,$perPage): LengthAwarePaginator;

    public function getIntervencao($task): Tasks;

    public function getIntervencaoById($taskId): Tasks;

    public function getIntervencaoFilter(string $searchString,int $tech,int $client,int $typeReport,int $work,string $ordenation,string $dateBegin,string $dateEnd,$perPage): LengthAwarePaginator;


    public function addIntervencao($object): Intervencoes;

    /*****/

    public function getTasksCompleted($all,$perPage);

    public function getTaskCompletedSearch($all,$searchString,$perPage): LengthAwarePaginator;

    public function getTasksFilterCompleted($all,string $searchString,int $tech,int $client,int $typeReport,int $work,string $ordenation,string $dateBegin,string $dateEnd,$perPage): LengthAwarePaginator;

    /*** EQUIPAMENTOS ***/
    public function getEquipments($id_client): object;

    public function getEquipmentBySerial($serialNumber): object;
    /**************** */

    /***** PRODUTOS *******/
    public function getProducts(): object;

    public function getProductByReference($reference): object;

    /************** */

    /**** EQUIPAMENTO *****/

    public function adicionarEquipamento($array): object;

    public function atualizarEquipamento($array): object;

    /******************* */

}
