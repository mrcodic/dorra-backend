<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use App\Services\BaseService;
use Illuminate\Contracts\View\{Factory, View};
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;


class DashboardController extends Controller
{
    private const BASE_FOLDER = 'dashboard.';
    protected $storeRequestClass;
    protected $updateRequestClass;
    protected $indexView;
    protected $createView;
    protected $editView;
    protected $showView;
    protected $assoiciatedData = [];
    protected $relationsToStore = [];
    protected $usePagination = false;
    protected $successMessage;

    public function __construct(public BaseService $service)
    {

    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|Factory|Application
    {
        $data = $this->service->getAll($this->usePagination);
        $associatedData = $this->assoiciatedData['index'] ?? [];
        return view(self::BASE_FOLDER . "$this->indexView", get_defined_vars());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|Application|Factory
    {
        return view(self::BASE_FOLDER . "{$this->createView}");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate($this->storeRequestClass->rules());
        $this->service->storeResource($validatedData,$this->relationsToStore);
        return Response::api();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View|Application|Factory
    {
        $model = $this->service->showResource($id);
        return view(self::BASE_FOLDER . "{$this->showView}", compact("model"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View|Application|Factory
    {
        $model = $this->service->showResource($id);
        return view(self::BASE_FOLDER . "{$this->editView}", compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $validatedData = $request->validate($this->updateRequestClass->rules($id));
        $this->service->updateResource($id, $validatedData);
        return to_route(self::BASE_FOLDER . "{$this->indexView}")
            ->with('success', $this->successMessage);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->service->deleteResource($id);
        return Response::api();

    }
}
