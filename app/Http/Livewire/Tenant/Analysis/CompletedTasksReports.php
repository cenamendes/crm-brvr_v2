<?php

namespace App\Http\Livewire\Tenant\Analysis;

use Livewire\Component;
use App\Models\Tenant\Tasks;
use Livewire\WithPagination;
use App\Exports\ExportTasksExcel;
use App\Models\Tenant\EstadoPedido;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use App\Events\Tasks\DispatchTasksToUser;
use App\Interfaces\Tenant\Tasks\TasksInterface;
use App\Interfaces\Tenant\Customers\CustomersInterface;
use App\Interfaces\Tenant\TeamMember\TeamMemberInterface;
use App\Interfaces\Tenant\Setup\Services\ServicesInterface;
use App\Interfaces\Tenant\TasksReports\TasksReportsInterface;
use App\Interfaces\Tenant\Analysis\CompletedAnalysisInterface;

class CompletedTasksReports extends Component
{
    use WithPagination;

    public int $perPage;
    public string $searchString = '';
    private ?object $tasksList = NULL;
    private ?object $counties = NULL;

    private TasksInterface $tasksInterface;
    private TasksReportsInterface $tasksReportsInterface;
    private ?object $task = NULL;
    public int $taskId = 0;

    /** Inicio do Filtro */
    protected object $analysisRepository;
    protected object $teamMembersRepository;
    protected object $customersRepository;
    protected object $serviceRepository;


    private ?object $analysis = NULL;
    private ?object $members = NULL;
    private ?object $customers = NULL;
    private ?object $service = NULL;
    private ?object $analysisToExcel = NULL;
    private ?object $estadosPedido =  NULL;

    public int $technical = 0;
    public int $client = 0;
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
    public function boot(CompletedAnalysisInterface $interfaceAnalysis,TasksInterface $tasksInterface, TasksReportsInterface $tasksReportsInterface, TeamMemberInterface $interfaceTeamMember, CustomersInterface $interfaceCustomers, ServicesInterface $interfaceService): Void
    {
        $this->tasksInterface = $tasksInterface;
        $this->tasksReportsInterface = $tasksReportsInterface;

        /** Inicio Filtro */
        $this->teamMembersRepository = $interfaceTeamMember;
        $this->customersRepository = $interfaceCustomers;
        $this->serviceRepository = $interfaceService;
        //** Fim filtro */

        $this->analysisRepository = $interfaceAnalysis;
    }

    /**
     * Livewire mount properties
     *
     * @return void
     */
    public function mount(): Void
    {
        $this->initProperties();
        $this->tasksList = $this->tasksInterface->getTasks($this->perPage);

        /**Parte do Filtro */
        $this->members = $this->teamMembersRepository->getTeamMembersAnalysis();
        $this->customers = $this->customersRepository->getCustomersAnalysis();
        $this->service = $this->serviceRepository->getServicesAnalysis();

        $this->estadosPedido = EstadoPedido::all();

        /**Parte do Fim do Filtro */

        $this->analysisToExcel = $this->analysisRepository->getAllAnalysisToExcel("");
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

    public function exportExcel($analysis)
    {
        //$this->analysis = $this->analysisRepository->getAllAnalysis($this->perPage);
        return Excel::download(new ExportTasksExcel($analysis), 'export-'.date('Y-m-d').'.xlsx');
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
                $this->tasksList = $this->tasksInterface->getTaskCompletedSearch("",$this->searchString,$this->perPage);
                $this->analysisToExcel = $this->analysisRepository->getAllAnalysisToExcelSearchString("",$this->searchString);
            }
            else {
                $this->tasksList = $this->tasksInterface->getTasksFilterCompleted("",$this->searchString,$this->technical,$this->client,$this->typeTask,$this->work,$this->ordenation,$this->dateBegin,$this->dateEnd,$this->perPage);
                $this->analysisToExcel = $this->analysisRepository->getAnalysisFilterToExcel("",$this->searchString,$this->technical,$this->client,$this->typeTask,$this->work,$this->ordenation,$this->dateBegin,$this->dateEnd);
            }
        }
        else 
        {
            if($this->flagRender == 0){
                $this->tasksList = $this->tasksInterface->getTasksCompleted("",$this->perPage);
                $this->analysisToExcel = $this->analysisRepository->getAllAnalysisToExcel("");
            }
            else {
                $this->tasksList = $this->tasksInterface->getTasksFilterCompleted("",$this->searchString,$this->technical,$this->client,$this->typeTask,$this->work,$this->ordenation,$this->dateBegin,$this->dateEnd,$this->perPage);
                $this->analysisToExcel = $this->analysisRepository->getAnalysisFilterToExcel("",$this->searchString,$this->technical,$this->client,$this->typeTask,$this->work,$this->ordenation,$this->dateBegin,$this->dateEnd);
            }
        }

        return view('tenant.livewire.analysis.completed-tasks-reports', [
            'tasksList' => $this->tasksList,
            'analysisExcel' => $this->analysisToExcel,
            'members' => $this->members,
            'customers' => $this->customers,
            'services' => $this->service,
            'estadosPedido' => $this->estadosPedido
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
