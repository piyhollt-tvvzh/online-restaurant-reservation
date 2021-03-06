<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Restaurant;
use App\Food_Menu;
use App\Review;
use App\User;
use Auth;

class RestaurantController extends Controller
{
    //
    public function show($id)
    {
        $restaurant = Restaurant::find($id);
        
        $food_menus = Food_Menu::where('restaurant_id', '=', $restaurant->id)->get();
        
        $food_categories = Food_Menu::select('category')->where('restaurant_id', '=', $restaurant->id)->get();
        
        $reviews = Review::where('restaurant_id', '=', $restaurant->id)->get();
        
        $reviews = $reviews->map(
            function($item, $key) {
                $user_name = ($item->user_id == null ? "Anonymous" : User::find($item->user_id)->user_name);
                $restaurant_name = Restaurant::find($item->restaurant_id)->name;
                return ['user_name' => $user_name, 'review_text' => $item->review_text, 'rating' => $item->rating];
            }
        );
        
        return view('restaurant.show', ['restaurant' => $restaurant,'reviews' => $reviews, 'food_menus' => $food_menus, 'food_categories' => $food_categories]);
    } 
    public function showAll()
    {
    	$restaurants = Restaurant::all();
    	return view('restaurant.showall', ['restaurants' => $restaurants]);	
    }
    
    public function search(Request $request)
    {
    	$name = $request->input('name');
    	$location = $request->input('location');
    	$cuisine = $request->input('cuisine');
    	$reservation_date = $request->input('reservation-date');
    	$timeslot = $request->input('reservation-time');
    	$num_of_persons = $request->input('num-of-persons');
        
        $query =  Restaurant::from('restaurant as r')->select('r.*');

    	if(strlen($location) != 0)
        {
            $query = $query->where('r.location', 'LIKE', '%'.$location.'%');
    	}
    	if(strlen($name) != 0)
    	{
    	    $query = $query->where('r.name', 'LIKE', '%'.$name.'%');
    	}
    	if($cuisine != 'none')
    	{
            $query = $query->where('c.cname', 'LIKE', $cuisine)
                ->join('offered_cuisine as oc', 'r.id', '=', 'oc.restaurant_id')
                ->join('cuisine as c', 'c.id', '=', 'oc.cuisine_id');
        }

    	$restaurants = $query->get();
    	
    	return view('restaurant.search', ['restaurants' => $restaurants]);
    }

    public function storeReview(Request $req, $id)
    {
	$review = new Review();
	$review->review_text = $req->input('new_review_text');
	$review->rating = $req->input('rating');
	$review->restaurant_id = $id;
	if(Auth::check())
	{
	    $review->user_id = Auth::user()->id;
	}
	$review->save();
	return redirect()->back();
    }
}
