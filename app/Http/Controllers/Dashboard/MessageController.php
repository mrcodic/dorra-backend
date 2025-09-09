<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\MessageService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

use App\Repositories\Interfaces\RoleRepositoryInterface;



class MessageController extends DashboardController
{
   public function __construct(public MessageService $messageService, public RoleRepositoryInterface $roleRepository)
   {
       parent::__construct($messageService);
       $this->indexView = 'messages.index';
       $this->usePagination = true;
       $this->assoiciatedData = [
           'index' => [
               'roles' => $this->roleRepository->all(),
           ]
       ];
       $this->resourceTable = 'messages';
   }

    public function getData()
    {
        return $this->messageService->getData();
   }

    public function reply($id,Request $request)
    {
         $this->messageService->reply($id,$request);
         return Response::api();

   }
}
