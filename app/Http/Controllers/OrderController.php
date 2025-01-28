<?php

namespace App\Http\Controllers;

use App\DataTables\OrderDataTable;
use App\Enums\OrderStatusEnum;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Repositories\OrderRepositoryInterface;
use App\Services\ProviderOrderService;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends BaseDataTableController
{
    private $orderRepository;
private $columns;
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;

    }
    /**
     * Handle the DataTable request.
     *
     * @param \App\DataTables\Custom\OrderDataTable $dataTable
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(\App\DataTables\Custom\OrderDataTable $dataTable, Request $request): \Illuminate\Http\JsonResponse|\Illuminate\View\View
    {
        // Handle AJAX request for data
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        // Return view with dynamic columns and filters
        return view('pages.conversation.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }
    /**
     * Define columns for the DataTable.
     */
    private function getColumns(): array
    {
        return [
            ['data' => 'id', 'searchable' => false],
            ['data' => 'name', 'searchable' => true],
            ['data' => 'email', 'searchable' => true],
            ['data' => 'phone', 'searchable' => false],
            [
                'data' => 'is_phone_verified',
                'searchable' => false,
                'backend' => fn($user) => $user->is_phone_verified ? 'Verified' : 'Not Verified',
            ],
            [
                'data' => 'gender',
                'searchable' => true,
                'backend' => fn($user) => $user->gender ? 'Male' : 'Female',
            ],
            [
                'data' => 'created_at',
                'searchable' => false,
                'backend' => fn($user) => $user->created_at->format('Y-m-d H:i:s'),
            ],
            [
                'data' => 'actions',
                'orderable' => false,
                'backend' => fn($model) => view('pages.order.columns._actions', compact('model'))->render(),
            ],
        ];
    }

    public function getData2(Request $request): \Illuminate\Http\JsonResponse
    {
        $users = User::query();
//        $users = User::select(['id', 'name', 'phone', 'email', 'is_phone_verified', 'gender', 'date_of_birth', 'created_at', 'country_id']);

        return DataTables::of($users)
            ->addIndexColumn() // Adds the DT_RowIndex column
            ->addColumn('country', function ($user) {
                return $user->country ? $user->country->name : 'N/A';
            })
            ->editColumn('gender', function ($user) {
                return $user->gender ? 'Male' : 'Female';
            })
            ->editColumn('is_phone_verified', function ($user) {
                return $user->is_phone_verified ? 'Verified' : 'Not Verified';
            })
            ->editColumn('created_at', function ($order) {
                return $order->created_at->format('Y-m-d H:i:s'); // Adjust format as needed
            })
            ->rawColumns(['country', 'gender', 'is_phone_verified'])
            ->make(true);
        $query = User::query(); // Replace with your desired model or query

        return DataTables::of($query)
            ->addIndexColumn() // Adds the DT_RowIndex column
            ->addColumn('actions', function ($row) {
                return '
                    <a href="#" class="btn btn-sm btn-primary edit-btn" data-id="' . $row->id . '">Edit</a>
                    <a href="#" class="btn btn-sm btn-danger delete-btn" data-id="' . $row->id . '">Delete</a>
                ';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('Y-m-d H:i:s');
            })
//            ->addIndexColumn()
            ->make(true);
    }


    /**
     * Display a listing of the resource.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function index2(OrderDataTable $dataTable)
    {
        return $dataTable->render('pages.order.2index');
    }


    public function index1()
    {
        $filters = [
            'name' => [
                'type' => 'text',
                'placeholder' => 'Search by Name',
            ],
            'gender' => [
                'type' => 'select',
                'label' => 'Gender',
                'options' => [
                    '1' => 'Male',  // key => value
                    '0' => 'Female',
                ],
            ],
            'email' => [
                'type' => 'text',
                'placeholder' => 'Search by Email',
            ],
            'created_at' => [
                'type' => 'date',
                'placeholder' => 'Select Date',
            ],
        ];
        $frontendColumns = $this->prepareFrontendColumns($this->getColumns());
        return view('pages.order.2index', compact('frontendColumns', 'filters'));
        // Pass columns for the frontend
        $frontendColumns = array_map(function ($column) {
            return [
                'data' => $column['data'],
                'name' => $column['name'],
                'title' => $column['title'],
                'searchable' => $column['searchable'] ?? false,
                'orderable' => $column['orderable'] ?? true,
            ];
        }, $this->columns);

        return view('pages.order.2index', compact('frontendColumns'));
    }

    public function getData()
    {
        return $this->handleDataTable(User::class, $this->getColumns());
    }

    private function handleDataTable2(string $modelClass, array $columns)
    {
        $query = $modelClass::query();

        $dataTable = DataTables::of($query)
            ->addIndexColumn() // Adds the DT_RowIndex column
            ->rawColumns(['actions']); // Mark raw columns to render HTML

        foreach ($columns as $column) {
            if (is_callable($column['backend'])) {
                $dataTable->editColumn($column['data'], $column['backend']);
            }
        }

        return $dataTable->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return view('pages.order.show', ['model' => $order]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        return view('pages.order.form', ['model' => $order]);
    }

    /**
     * Update the specified resource in storage.
     * @throws ExceptionInterface
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        if ($request->status == OrderStatusEnum::DONE->value) {
            if(!$order->invoice|| $order->invoice->unpaidAmount() > 0){
                return ['status' => false, 'msg' => 'Invoice is not paid'];
            }
            $provider = $order->provider;
            $orderService = app(ProviderOrderService::class);
            $response = $orderService->updateOrderToDone($request, $order->id, $provider);
            if (!$response['status']){
                return ['status' => false, 'msg' => $response['message']];
            }else{
                return ['status' => true, 'msg' => 'تم التعديل بنجاح'];
            }
        }
        if ($request->status == OrderStatusEnum::CANCELED->value) {
            $order->is_confirmed = 0;
            $order->status = OrderStatusEnum::CANCELED;
            $order->save();
            return ['status' => true, 'msg' => 'تم التعديل بنجاح'];
        }
        return ['status' => false, 'msg' =>'لا يمكن تعديل حالة الطلب'];

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
