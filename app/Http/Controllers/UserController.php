<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\UserLoan;
use App\UserEmi;
use Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    use \App\Traits\APIResponseManager;

    protected $user;
    protected $userLoan;
    protected $user_emi;

    public function __construct(
        User $user,
        UserLoan $userLoan,
        UserEmi $userEmi
    )
    {
        $this->user = $user;
        $this->userLoan = $userLoan;
        $this->userEmi = $userEmi;
    }

    /**
     * Desc: Method is used to registered the user
     * Param: {phone ,password}
    */
    public function signup(Request $request) {
    
        $rule = [];

        // Check Validation
        try{
            
            $this->validate($request, [
                'name' => 'required',
                'phone' => 'required|numeric|min:10',
                'password' => 'required|string|min:6'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            $errorResponse = $this->validationResponse($e);
            return $this->responseManager(400, 'Validation Error', $errorResponse);

        }

        try {

            $loan_limit = $this->user->accountLimit($request->phone);

            if($loan_limit > 1) {
                return $this->responseManager(200, 'YOU_ALREADY_HAVE_REGISTERED');
            }
            
            $data = [
                'customer_id' => mt_rand(1000000, 9999999),
                'name' => $request->name,
                'phone' => $request->phone,
                'password' =>  Hash::make($request->password)
            ];

            $this->user->add($data);

            return $this->responseManager(200, 'USER_ADDED_SUCCESSFULLY', $data);

        } catch (\PDOException $e) {
            $errorResponse = $e->getMessage();
            return $this->responseManager(402, 'ERROR', $errorResponse);
        } 

    }

    
    /**
     * Desc: Method is used to authenticate the user
     * Param: {customer_id, password}
     */
    public function login(Request $request) {

        // Check Validation
        try{
            
            $this->validate($request, [
                'customer_id' => 'required|exists:users,customer_id',
                'password' => 'required'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            $errorResponse = $this->validationResponse($e);
            return $this->responseManager(400, 'Validation Error', $errorResponse);

        }


        // Authenticate User
        try{

            // Valdate Auth
            if ( 
                !Auth::attempt([
                    'customer_id' => $request->customer_id, 
                    'password' => $request->password
                ]) 
            ) 
            {
                return $this->responseManager(400, 'USER _INVALID_CREDENTIALS');
            }
            

            // User Check
            $user=Auth::user();
            
            if( !empty($user) ){

                // Create Token
                $token = $this->user->createPassportToken($user);
                $user->access_token=$token; 

                return $this->responseManager(200, 'USER_LOGIN_SUCCESS', ['authtoken' => $token]);
            } 
            else 
            {
                return $this->responseManager(402, 'ACCOUNT_NOT_VERIFY');
            }

        } catch (\PDOException $e) {

            $errorResponse = $e->getMessage();

            return $this->responseManager(402, 'DB_ERROR', $errorResponse);

        }
        
    }


    /**
     * Desc: Method is used to apply loan by user
     * Param: {amount, loan_term, $auth-token(header)}
     */
    public function applyLoan(Request $request) {

        // Check Validation
        try{
            
            $this->validate($request, [
                'amount' => 'required|integer|between:99,1000000',
                'loan_term' => 'required|integer|between:4,96'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            $errorResponse = $this->validationResponse($e);
            return $this->responseManager(400, 'Validation Error', $errorResponse);

        }

        // Authenticate User
        try{

            $currentUser = Auth::user();
            if(empty($currentUser)){
                return $this->responseManager(402, 'USER_DOES_NOT_EXIST, LOGIN_AGAIN');
            }
           
            $user_id = $currentUser->id;
            $loan_limit = DB::table('users_loan')->where('user_id', $user_id)->count();

            if($loan_limit < 10) {

                $emi_amount = ceil($request->amount / $request->loan_term); 
                
                $loan_data = [
                    'user_id' => $currentUser->id,
                    'loan_amount' => $request->amount,
                    'loan_term' => $request->loan_term,
                    'approved' => 1,
                    'closed' => 0
                ];

                $loan_id = $this->userLoan->add($loan_data);

                $emi_response = [];

                for($i=0; $i<$request->loan_term; $i++){

                    $emi_data = [
                        'user_id' => $currentUser->id,
                        'loan_id' => $loan_id,
                        'emi_amount' => $emi_amount,
                        'week' => $i+1,
                        'payment_status' => 0,
                    ];

                    $this->userEmi->add($emi_data);

                    $emi_response[] = $emi_data;
                }
                return $this->responseManager(200, 'LOAN_APPLIED_SUCCESSFULLY', $emi_response);
            } 
            else {
                return $this->responseManager(400, 'YOU_ARE_NOT_ALLOWED_TO_APPLY_LOAN_UPTO_10');
            }
            

        } catch (\PDOException $e) {

            $errorResponse = $e->getMessage();

            return $this->responseManager(402, 'DB_ERROR', $errorResponse);

        }
        
    }


    /**
     * Desc: Method is used to pay-emi by user
     * Param: {amount}
     */
    public function payEmi(Request $request) {

        // Check Validation
        try{
            
            $this->validate($request, [
                'amount' => 'required|integer|between:1,1000000|exists:users_emi,emi_amount',
                'loan_id' => 'required|exists:users_loan,id',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            $errorResponse = $this->validationResponse($e);
            return $this->responseManager(400, 'Validation Error', $errorResponse);

        }

        // Authenticate User
        try{

            $currentUser = Auth::user();
            if(empty($currentUser)){
                return $this->responseManager(402, 'USER_DOES_NOT_EXIST, LOGIN_AGAIN');
            }
           
            $user_id = $currentUser->id;
            $loan_id = $request->loan_id;
            $emi_amount = $request->amount;

            $emi_data = DB::table('users_emi')
                ->where('user_id', $user_id)
                ->where('loan_id', $loan_id)
                ->where('payment_status', 0)
                ->get()
                ->toArray();

            if(empty($emi_data)) {
                return $this->responseManager(402, ' YOU_ALREADY_PAID_YOUR_EMI');
            }

            $latest_week_emi_id = $emi_data[0]->id;
            
            DB::table('users_emi')
            ->where('id', $latest_week_emi_id)
            ->update(['payment_status' => 1]);
            
            if(count($emi_data) == 1) {
                DB::table('users_loan')
                ->where('id', $loan_id)
                ->update(['closed' => 1]);
            }
                
            return $this->responseManager(200, 'YOUR_EMI__SUCCESSFULLY_PAID');

        } catch (\PDOException $e) {

            $errorResponse = $e->getMessage();

            return $this->responseManager(402, 'DB_ERROR', $errorResponse);

        }
        

    }
    
}
