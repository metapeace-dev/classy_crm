<?php

namespace App\Http\Controllers\Installer;

use App\Client;
use App\ClientDetails;
use App\Helper\Reply;
use App\Http\Requests\Admin\Client\StoreClientRequest;
use App\Http\Requests\Admin\Client\UpdateClientRequest;
use App\Invoice;
use App\Lead;
use App\Notifications\NewUser;
use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class InstallerClientsController extends InstallerBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.clients');
        $this->pageIcon = 'icon-people';

        $this->middleware(function ($request, $next) {
            if (!in_array('clients', $this->user->modules)) {
                abort(403);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->clients = Client::all();

        return view('installer.clients.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort(403);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClientRequest $request)
    {
        abort(403);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->client = Client::findOrFail($id);
        $this->states = Config::get('constants.states');
        return view('installer.clients.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        abort(403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClientRequest $request, $id)
    {
        abort(403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        abort(403);
    }

    public function data(Request $request)
    {
        abort(403);
    }

    public function showProjects($id) 
    {
        $this->client = User::withoutGlobalScope('active')->findOrFail($id);
        return view('installer.clients.projects', $this->data);
    }

    public function showInvoices($id)
    {
        if (!$this->user->can('view_invoices')) {
            abort(403);
        }
        $this->client = User::withoutGlobalScope('active')->findOrFail($id);
        $this->invoices = Invoice::join('projects', 'projects.id', '=', 'invoices.project_id')
            ->join('currencies', 'currencies.id', '=', 'invoices.currency_id')
            ->join('users', 'users.id', '=', 'projects.client_id')
            ->select('invoices.invoice_number', 'invoices.total', 'currencies.currency_symbol', 'invoices.issue_date', 'invoices.id')
            ->where('projects.client_id', $id)
            ->get();

        return view('installer.clients.invoices', $this->data);
    }

    public function export()
    {
        $rows = User::leftJoin('client_details', 'users.id', '=', 'client_details.user_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.mobile',
                'client_details.company_name',
                'client_details.address',
                'client_details.website',
                'users.created_at'
            )
            ->get();

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['ID', 'Name', 'Email', 'Mobile', 'Company Name', 'Address', 'Website', 'Created at'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($rows as $row) {
            $exportArray[] = $row->toArray();
        }

        // Generate and return the spreadsheet
        Excel::create('clients', function ($excel) use ($exportArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Clients');
            $excel->setCreator('Worksuite')->setCompany($this->companyName);
            $excel->setDescription('clients file');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function ($sheet) use ($exportArray) {
                $sheet->fromArray($exportArray, null, 'A1', false, false);

                $sheet->row(1, function ($row) {

                    // call row manipulation methods
                    $row->setFont(array(
                        'bold'       =>  true
                    ));
                });
            });
        })->download('xlsx');
    }
}
