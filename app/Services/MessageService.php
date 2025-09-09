<?php

namespace App\Services;



use App\Mail\MessageReplyMail;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use Illuminate\Support\Facades\Mail;

use Yajra\DataTables\Facades\DataTables;

class MessageService extends BaseService
{
    public function __construct(MessageRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
    public function getData()
    {
        $messages = $this->repository->query()
            ->when(request()->filled('search_value'), function ($query) {
                $search = request('search_value');
                $words = preg_split('/\s+/', $search);
                $query->where(function ($query) use ($words) {
                    foreach ($words as $word) {
                        $query->where(function ($q) use ($word) {
                            $q->where('email', 'like', '%' . $word . '%');
                        });
                    }
                });
            })
            ->orderBy('created_at', request('created_at', 'desc'));

        return DataTables::of($messages)
            ->editColumn('created_at', function ($message) {
                return $message->created_at->format('d/m/Y') ;
            })
            ->make();
    }

    public function reply($id, $request)
    {
       $message =  $this->repository->find($id);
        $validatedData = $request->validate(['reply' => ['required', 'string', 'min:3' ,'max:100']]);
        Mail::to($message->email)->send(new MessageReplyMail($validatedData['reply']));

    }
}
