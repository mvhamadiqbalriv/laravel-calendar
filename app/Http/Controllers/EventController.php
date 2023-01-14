<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventRequest;
use App\Http\Requests\UpdateEventRequest;
use Illuminate\Http\Request;
use App\Http\Services\EventService;
use App\Http\Services\GoogleService;
use App\Models\Event;
use Carbon\Carbon;
use Google\Service\CloudSourceRepositories\Repo;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $googleService = new GoogleService(auth()->user());
        // dd($googleService->authUrl());
        return view('events.list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateEventRequest $request)
    {
        $data = $request->all();
        $data['user_id'] = auth()->user()->id;
        $eventService = new EventService(auth()->user());
        $event = $eventService->create($data);
        if ($event) {
            return response()->json([
                'success' => true
            ]);
        }else{
            return response()->json([
                'success' => false
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEventRequest $request, $id)
    {
        $data = $request->all();
        $eventService = new EventService(auth()->user());
        $event = $eventService->update($id, $data);
        if ($event) {
            return response()->json([
                'success' => true
            ]);
        }else{
            return response()->json([
                'success' => false
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        try {
            // if($event->event_id){
            //     $eventService = new EventService(auth()->user());
            //     $eventService->syncWithGoogle($event, true);
            // }
            $event->delete();
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            return response()->json(['success' => true]);
        }
    }

    public function refetchEvents(Request $request)
    {
        $eventService = new EventService(auth()->user());
        $eventsData = $eventService->allEvents($request->all());
        return response()->json($eventsData);
    }

    public function resizeEvent(Request $request, $id)
    {
        $data = $request->all();
        if (isset($data['is_all_day']) && $data['is_all_day'] == 1) {
            $data['end'] = Carbon::createFromTimestamp(strtotime($data['end']))->addDays(-1)->toDateString();
        }
        $eventService = new EventService(auth()->user());
        $event = $eventService->update($id, $data);
        if ($event) {
            return response()->json([
                'success' => true
            ]);
        }else{
            return response()->json([
                'success' => false
            ]);
        }
    }
}
