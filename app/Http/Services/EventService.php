<?php 

namespace App\Http\Services;
use Carbon\Carbon;
use App\Models\Event;

class EventService {
    protected $user;
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function create($data)
    {
        // if (isset($data['is_all_day']) && $data['is_all_day'] == 1) {
        //     $data_diff = Carbon::createFromTimestamp(strtotime($data['end']))->diffInDays(Carbon::createFromTimestamp(strtotime($data['start'])));
        //     $data['end'] = Carbon::createFromTimestamp(strtotime($data['start']))->addDays($date_diff)->toDateString();
        // }
        $event = new Event($data);
        $event->save();
        // SyncEventWithGoolge::dispatch($event, $this->user);
        return $event;
    }

    public function update($id, $data)
    {
       $event = Event::find($id);
       $event->fill($data);
       $event->save();
       return $event;
    }

    public function allEvents($filters)
    {
        $eventQuery = Event::query();
        $eventQuery->where('user_id', $this->user->id);
        if ($filters['start']) {
            $eventQuery->where('start', '>=', $filters['start']);
        }
        if ($filters['end']) {
            $eventQuery->where('end', '<=', $filters['end']);
        }
        $events = $eventQuery->get();
        $data = [];
        foreach ($events as $key => $event) {
            if (!(int)$event['is_all_day']) {
                $event['allDay'] = false;
                $event['start'] = Carbon::createFromTimestamp(strtotime($event['start']))->toDateTimeString();
                $event['end'] = Carbon::createFromTimestamp(strtotime($event['end']))->toDateTimeString();
                $event['endDay'] = $event['end'];
                $event['startDay'] = $event['start'];
            }else{
                $event['allDay'] = true;
                $event['endDay'] = Carbon::createFromTimestamp(strtotime($event['end']))->addDays(-1)->toDateString();
                $event['startDay'] = $event['start'];
            }
            $event['event_id'] = $event['id'];
            array_push($data, $event);
        }

        return $data;
    }
}