<?php

namespace App\Http\Livewire\Tenant\Tasks;

use Exception;
use Livewire\Component;
use App\Models\Tenant\Tasks;
use Livewire\WithPagination;
use App\Models\Tenant\Pedidos;
use App\Models\Tenant\EstadoPedido;
use App\Models\Tenant\Intervencoes;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use App\Events\Alerts\SendStatusEvent;
use App\Events\Tasks\DispatchTasksToUser;
use App\Events\Alerts\CheckFinalizadosEvent;
use App\Events\Alerts\ReaberturaPedidoEvent;
use App\Interfaces\Tenant\Tasks\TasksInterface;
use App\Interfaces\Tenant\Customers\CustomersInterface;
use App\Interfaces\Tenant\TeamMember\TeamMemberInterface;
use App\Interfaces\Tenant\Setup\Services\ServicesInterface;
use App\Interfaces\Tenant\TasksReports\TasksReportsInterface;
use App\Interfaces\Tenant\CustomerLocation\CustomerLocationsInterface;

class ShowTasks extends Component
{
    use WithPagination;

    public int $perPage;
    public string $searchString = '';
    private ?object $tasksList = NULL;
    private ?object $counties = NULL;


    protected $listeners = ['dispatchTask' => 'dispatchTask','enviaStatus' => 'enviaStatus'];

    private TasksInterface $tasksInterface;
    private TasksReportsInterface $tasksReportsInterface;
    private ?object $task = NULL;
    public int $taskId = 0;

    /** Inicio do Filtro */
    protected object $analysisRepository;
    protected object $teamMembersRepository;
    protected object $customersRepository;
    protected object $serviceRepository;
    protected object $locationRepository;

    private ?object $analysis = NULL;
    private ?object $members = NULL;
    private ?object $customers = NULL;
    private ?object $service = NULL;
    private ?object $analysisToExcel = NULL;
    private ?object $estadosPedido =  NULL;

    public int $technical = 0;
    public string $client = "";
    public int $work = 0;
    public int $typeTask = 0;
    public string $ordenation = '';

    public string $dateBegin = '';
    public string $dateEnd = '';

    public int $flagRender = 0;
    /** Fim do Filtro  */

    /**
     * Livewire construct function
     *
     * @param TasksInterface $tasksInterface
     * @return Void
     */
    public function boot(TasksInterface $tasksInterface, TasksReportsInterface $tasksReportsInterface, TeamMemberInterface $interfaceTeamMember, CustomersInterface $interfaceCustomers, ServicesInterface $interfaceService, CustomerLocationsInterface $locationInterface): Void
    {
        $this->tasksInterface = $tasksInterface;
        $this->tasksReportsInterface = $tasksReportsInterface;
        $this->locationRepository = $locationInterface;

        /** Inicio Filtro */
        $this->teamMembersRepository = $interfaceTeamMember;
        $this->customersRepository = $interfaceCustomers;
        $this->serviceRepository = $interfaceService;
        //** Fim filtro */

    }

    /**
     * Livewire mount properties
     *
     * @return void
     */
    public function mount(): Void
    {
        $this->initProperties();
        // $this->tasksList = $this->tasksInterface->getTasks($this->perPage);

        /**Parte do Filtro */
        // $this->members = $this->teamMembersRepository->getTeamMembersAnalysis();
        // $this->customers = $this->customersRepository->getCustomersAnalysis();
        // $this->service = $this->serviceRepository->getServicesAnalysis();

        $this->estadosPedido = EstadoPedido::all();

        /**Parte do Fim do Filtro */

    }

    /**
     * Ask if the customer wants to schedule the task
     *
     * @return Void
     */
    public function askToSchedule($id): Void
    {
        $this->taskId = $id;
        $this->dispatchBrowserEvent('swalModalQuestion', [
            'title' => __('Schedule Task'),
            'message' => __('Do you want to schedule the task?'),
            'status' => 'question',
            'confirm' => 'true',
            'confirmButtonText' => __('Yes, schedule the task!'),
            'cancellButtonText' => __('No, do not schedule the task!'),
            'function' => "dispatchTask"
        ]);
        $this->skipRender();
    }

    /**
     * Schedule the task
     *
     * @return Void
     */
    public function dispatchTask(): Void
    {
        $this->task = $this->tasksInterface->getTaskById($this->taskId);
        $this->task->status = 1;
        $error = true;
        if (!$this->task->scheduled_date) {
            if ($this->task->preview_date > date('Y-m-d')) {
                $this->task->scheduled_date = $this->task->preview_date;
                $this->task->scheduled_hour = $this->task->preview_hour;
                $error = false;
            } else if ($this->task->preview_date == date('Y-m-d') && ($this->task->preview_hour > date('H:i:s'))) {
                $this->task->scheduled_date = $this->task->preview_date;
                $this->task->scheduled_hour = $this->task->preview_hour;
                $error = false;
            } else {
                $this->dispatchBrowserEvent('swalModalQuestion', ['title' => __('Tasks'), 'message' => __('You must set the schedule date and time!'), 'status'=>'error']);
            }
        } else if ($this->task->scheduled_date) {
            // if ($this->task->scheduled_date < date('Y-m-d')) {
            //     $this->dispatchBrowserEvent('swalModalQuestion', ['title' => __('Tasks'), 'message' => __('Cannot assign a task in the past!'), 'status'=>'error']);
            // } else {
            //     $error = false;
            // }
            $error = false;
        }

        $updateTask = false;

        $tasksReports = $this->tasksReportsInterface->getReportByTaskId($this->task->id);

        if($tasksReports !== NULL) {
            if($tasksReports->reportStatus > 0) {
                $this->dispatchBrowserEvent('swalModalQuestion', ['title' => __('Tasks'), 'message' => __('This task already has a report!'), 'status'=>'error']);
                $error = true;
            } else {
                $updateTask = true;
            }
        }

        if ( $error == false)
        {
            if($updateTask === false) {
                if($this->tasksInterface->dispatchTask($this->task)) {
                    event(new DispatchTasksToUser(Tasks::with('servicesToDo')->with('tech')->with("taskCustomer")->with('taskLocation')->where('id',$this->task->id)->first()));
                    File::delete('marcacao-'.$this->task->reference.'.vcs');
                    $this->dispatchBrowserEvent('swalModalQuestion', ['title' => __('Tasks'), 'message' => __('Task scheduled with success!'), 'status'=>'info']);
                } else {
                    $this->dispatchBrowserEvent('swalModalQuestion', ['title' => __('Tasks'), 'message' => __('Error while scheduling task!'), 'status'=>'error']);
                }
            } else {
               if($this->tasksReportsInterface->updateTaskReport($this->task)) {
                event(new DispatchTasksToUser(Tasks::with('servicesToDo')->with('tech')->with("taskCustomer")->with('taskLocation')->where('id',$this->task->id)->first()));
                $this->dispatchBrowserEvent('swalModalQuestion', ['title' => __('Tasks'), 'message' => __('Task scheduled with success!'), 'status'=>'info']);
                } else {
                    $this->dispatchBrowserEvent('swalModalQuestion', ['title' => __('Tasks'), 'message' => __('Error while scheduling task!'), 'status'=>'error']);
                }
            }


        }
        $this->tasksList = $this->tasksInterface->getTasks($this->perPage);
    }

    /**
     * Change number of records to display
     *
     * @return void
     */
    public function updatedPerPage(): void
    {
        $this->resetPage();
        session()->put('perPage', $this->perPage);
        $this->tasksList = $this->tasksInterface->getTasks($this->perPage);
    }

    /**
     * Do a search base on
     *
     * @return void
     */
    public function updatedSearchString(): void
    {
        $customer = $this->customersRepository->getSearchedCustomerCollection($this->searchString);

        $this->resetPage();
    }

    /**
     * Create custom pagination html string
     *
     * @return string
     */
    public function paginationView(): String
    {
        return 'tenant.livewire.setup.pagination';
    }

    /** Parte fo Filtro **/

    public function updatedTechnical(): void
    {
        $this->flagRender = 1;
    }

    public function updatedClient(): void
    {
        $this->flagRender = 1;
    }

    public function updatedWork(): void
    {
        $this->flagRender = 1;
    }

    public function updatedTypeTask(): void
    {
        $this->flagRender = 1;
    }

    public function updatedOrdenation(): void
    {
        $this->flagRender = 1;
    }

    public function updatedDateBegin(): void
    {
        $this->dispatchBrowserEvent("contentChanged");

        $this->flagRender = 1;
    }

    public function updatedDateEnd(): void
    {
        $this->dispatchBrowserEvent("contentChanged");

        $this->flagRender = 1;
        $this->resetPage();
    }

    public function clearFilter(): void
    {
        $this->members = $this->teamMembersRepository->getTeamMembersAnalysis();
        $this->customers = $this->customersRepository->getCustomersAnalysis();
        $this->service = $this->serviceRepository->getServicesAnalysis();


        $this->technical = 0;
        $this->client = 0;
        $this->work = 0;
        $this->typeTask = 0;
        $this->dateBegin = '';
        $this->dateEnd = '';
        $this->ordenation = 'desc';

        $this->flagRender = 0;
    }

    /****FIM DO FILTRO ****/

    public function enviaStatus($id,$mensagem)
    {
        $pedido = Pedidos::where('id',$id)->with('tipoEstado')->with('customer')->with('tech')->with('tipoPedido')->first();

        $emailCheck = event(new SendStatusEvent($pedido,$mensagem));



        if($emailCheck[0] == null)
        {
            $this->dispatchBrowserEvent('fireSwal', ['title' => "Estado", 'message' => "Enviado email para cliente", 'status'=>'success']);
        }
        else {
            $this->dispatchBrowserEvent('fireSwal', ['title' => "Estado", 'message' => "Esse cliente não tem email", 'status'=>'error']);
        }


        
    }

    public function reabrirPedido($id)
    {
        $pedido = Pedidos::where('id',$id)->with('tipoEstado')->first();

        Pedidos::where('id',$id)->update([
            "estado" => 6
        ]);

        event(new ReaberturaPedidoEvent($pedido));

        return redirect()->route('tenant.tasks.edit', $pedido->id);
    }

    public function finalizarPedido($id)
    {
        $getIntervencao = Intervencoes::where('id_pedido',$id)->orderBy('id','desc')->first();

        event(new CheckFinalizadosEvent($getIntervencao));

        $this->dispatchBrowserEvent('fireSwal', ['title' => "Estado", 'message' => "Pedido finalizado com sucesso", 'status'=>'success']);
    }

    public function abrirPopUp($id)
    {
        $message = "<div class='swalBox'>";
        $title = "<h3 style='color: #595959; font-size:1.8rem; font-weight: 600;'>Tem a certeza?</h3>";
        $message .= "<p style='color: #595959; font-size:1rem; font-weight: 400;'>Adicione uma descrição</p>";
        $message .= "<textarea id='qtdrececionada' style='font-size:1rem;border: 0.1px solid black; border-radius: 0.2rem; padding:0.4rem; width: 100%; color: #495057;background-color: #fff;background-clip: padding-box;border: 1px solid #f0f1f5;border-radius: 0.75rem;'></textarea>";
        $message .= "<input type='hidden' id='itemId' value=" . $id . ">";
        $message .= "</label>";

        $this->dispatchBrowserEvent('abrirPopUp', [
            'title' => $title,
            'message' => $message,
            'status' => 'info',
            'itemId' => $id,
        ]);
    }

   
    /**
     * Livewire render list tasks view
     *
     * @return View
     */
    public function render(): View
    {
      

        /** Parte do Filtro */
        $this->members = $this->teamMembersRepository->getTeamMembersAnalysis();
        $this->customers = $this->customersRepository->getCustomersAnalysis();
        $this->service = $this->serviceRepository->getServicesAnalysis();
        $this->estadosPedido = EstadoPedido::all();


        if($this->searchString != null)
        {
            if($this->flagRender == 0){
                $this->tasksList = $this->tasksInterface->getTaskSearch($this->searchString,$this->perPage);
            }
            else {
                $this->tasksList = $this->tasksInterface->getTasksFilter($this->searchString,$this->technical,$this->client,$this->typeTask,$this->work,$this->ordenation,$this->dateBegin,$this->dateEnd,$this->perPage);
            }
        }
        else 
        {
            if($this->flagRender == 0){
                $this->tasksList = $this->tasksInterface->getTasks($this->perPage);
            }
            else {
                $this->tasksList = $this->tasksInterface->getTasksFilter($this->searchString,$this->technical,$this->client,$this->typeTask,$this->work,$this->ordenation,$this->dateBegin,$this->dateEnd,$this->perPage);
            }
        }


        return view('tenant.livewire.tasks.show', [
            'tasksList' => $this->tasksList,
            'members' => $this->members,
            'customers' => $this->customers,
            'services' => $this->service,
            'estadosPedido' => $this->estadosPedido,
            'customersRepository' => $this->customersRepository,
            'locationRepository' => $this->locationRepository
        ]);

        /** Fim do Filtro */
    }

    /**
     * Prepare properties
     *
     * @return void
     */
    private function initProperties(): void
    {
        if (isset($this->perPage)) {
            session()->put('perPage', $this->perPage);
        } elseif (session('perPage')) {
            $this->perPage = session('perPage');
        } else {
            $this->perPage = 10;
        }
    }

}
