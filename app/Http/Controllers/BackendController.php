<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Enjoythetrip\Interfaces\BackendRepositoryInterface; 
use App\Enjoythetrip\Gateways\BackendGateway; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Storage; 
use App\Events\ReservationConfirmedEvent; 
use Illuminate\Support\Facades\Cache; 


class BackendController extends Controller
{
    use \App\Enjoythetrip\Traits\Ajax; 

    
    public function __construct(BackendGateway $backendGateway, BackendRepositoryInterface $backendRepository)
    {

        $this->middleware('CheckOwner')->only(['confirmReservation','saveRoom','saveObject','myObjects']);

        $this->bG = $backendGateway;
        $this->bR = $backendRepository;
    }


    
    public function index(Request $request )
    {
        $objects = $this->bG->getReservations($request); 
        return view('backend.index',['objects'=>$objects]);
    }

    
    public function myobjects(Request $request )
    {
        $objects = $this->bR->getMyObjects($request); 

        return view('backend.myobjects',['objects'=>$objects]);
    }

    
    public function profile(Request $request )
    {
        
        if ($request->isMethod('post'))
        {

            $user = $this->bG->saveUser($request);

            if ($request->hasFile('userPicture'))
            {
                $path = $request->file('userPicture')->store('users', 'public'); 

                
                if (count($user->photos) != 0)
                {
                    $photo = $this->bR->getPhoto($user->photos->first()->id);

                    Storage::disk('public')->delete($photo->storagepath);
                    $photo->path = $path;

                    $this->bR->updateUserPhoto($user,$photo);

                }
                else
                {
                    $this->bR->createUserPhoto($user,$path);
                }

            }


            return redirect()->back();
        }

        return view('backend.profile',['user'=>Auth::user()]);
    }

    
    public function deletePhoto($id)
    {

        $photo = $this->bR->getPhoto($id); 

        $this->authorize('checkOwner', $photo);

        $path = $this->bR->deletePhoto($photo); 

        Storage::disk('public')->delete($path); 

        Cache::flush(); 

        return redirect()->back();
    }


    
    public function saveobject($id = null, Request $request)
    {
        
        if($request->isMethod('post'))
        {
            if($id)
            $this->authorize('checkOwner', $this->bR->getObject($id));

            $this->bG->saveObject($id, $request);

            Cache::flush(); 

            if($id)
            return redirect()->back();
            else
            return redirect()->route('myObjects');

        }


        
        if($id)
        return view('backend.saveobject',['object'=>$this->bR->getObject($id),'cities'=>$this->bR->getCities()]);
        else
        return view('backend.saveobject',['cities'=>$this->bR->getCities()]);
    }

    
    public function saveRoom($id = null, Request $request)
    {

        if($request->isMethod('post'))
        {
            if($id) // editing room
            $this->authorize('checkOwner', $this->bR->getRoom($id));
            else // adding a new room
            $this->authorize('checkOwner', $this->bR->getObject($request->input('object_id')));

            $this->bG->saveRoom($id, $request);

            Cache::flush(); 

            if($id)
            return redirect()->back();
            else
            return redirect()->route('myObjects');

        }

        if($id)
        return view('backend.saveroom',['room'=>$this->bR->getRoom($id)]);
        else
        return view('backend.saveroom',['object_id'=>$request->input('object_id')]);
    }

    
    public function deleteRoom($id)
    {
        $room =  $this->bR->getRoom($id); 

        $this->authorize('checkOwner', $room); 
        $this->bR->deleteRoom($room); 

        Cache::flush(); 

        return redirect()->back(); 
    }



    public function confirmReservation($id)
    {
        $reservation = $this->bR->getReservation($id);

        $this->authorize('reservation', $reservation);

        $this->bR->confirmReservation($reservation);

        $this->flashMsg ('success', __('Reservation has been confirmed')); 

        event( new ReservationConfirmedEvent($reservation) ); 

        if (!\Request::ajax())
        return redirect()->back(); 
    }


    
    public function deleteReservation($id)
    {
        $reservation = $this->bR->getReservation($id); 

        $this->authorize('reservation', $reservation); 

        $this->bR->deleteReservation($reservation); 

        $this->flashMsg ('success', __('Reservation has been deleted'));  

        if (!\Request::ajax()) 
        return redirect()->back();
    }


    public function deleteArticle($id)
    {
        $article =  $this->bR->getArticle($id); 

        $this->authorize('checkOwner', $article); 

        $this->bR->deleteArticle($article); 

        Cache::flush(); 

        return redirect()->back(); 
    }


    public function saveArticle($object_id = null, Request $request )
    {
        
        if(!$object_id)
        {
           $this->flashMsg ('danger', __('First add an object'));
           return redirect()->back();
        }

        $this->authorize('checkOwner', $this->bR->getObject($object_id)); 

        $this->bG->saveArticle($object_id,$request); 

        Cache::flush(); 

        return redirect()->back(); 
    }


    
    public function deleteObject($id)
    {
        $this->authorize('checkOwner', $this->bR->getObject($id));

        $this->bR->deleteObject($id);

        Cache::flush(); 

        return redirect()->back();

    }


    public function getNotifications()
    {
        return response()->json( $this->bR->getNotifications() ); 
    }


    public function setReadNotifications(Request $request)
    {
        return  $this->bR->setReadNotifications($request);
    }


}

