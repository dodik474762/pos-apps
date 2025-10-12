<?php

namespace App\Http\Controllers\web\auth;

use App\Http\Controllers\Controller;
use App\Models\Master\Users;
use App\Models\Master\UsersPermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    //
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function index(Request $request)
    {
        $data = $request->all();
        return view('web.login.index', $data);
    }

    public function changePassword(Request $request)
    {
        $data = $request->all();
        return view('web.login.passwordreset', $data);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'username' => $request->get('username'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201);
    }


    public function signIn(Request $request)
    {
        $data = $request->all();
        $credentials = $request->only('username', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return redirect()->action([LoginController::class, 'index'], ['error' => 'Pengguna Tidak Ditemukan']);
            }
        } catch (JWTException $e) {
            return redirect()->action([LoginController::class, 'index'], ['error' => 'Pengguna Tidak Valid, Tidak Dapat Login.']);
        }
        // $user = $this->getAuthenticatedUser();

        $userdata = DB::table('users as usr')
            ->select([
                'usr.*',
                'ha.group as akses',
                'kry.company',
                'ut.nama_company',
                'kry.nama_lengkap',
                'kry.group as group_karyawan',
                'dic.keterangan as group_karyawan_name',
                'kry.id as id_karyawan'
            ])
            ->join('karyawan as kry', 'kry.nik', 'usr.nik')
            ->join('company as ut', 'ut.id', 'kry.company')
            ->join('users_group as ha', 'ha.id', '=', 'usr.user_group')
            ->leftJoin('dictionary as dic', 'dic.term_id', '=', 'kry.group')
            ->where(function ($query) use ($data) {
                return $query->where('usr.username', '=', $data['username'])
                    ->orWhere('usr.nik', '=', $data['username']);
            })
            ->whereNull('usr.deleted')
            ->first();

        if (!empty($userdata)) {
            $dataMenu = UsersPermission::where('users_permissions.users_group', $userdata->user_group)
                ->select([
                    'users_permissions.*',
                    'am.nama as menu'
                ])
                ->join('menu as am', 'am.id', '=', 'users_permissions.menu')
                ->whereNull('users_permissions.deleted')
                ->get()->toArray();

            $result_akses = [];
            foreach ($dataMenu as $key => $value) {
                $value['id_menu'] = strtolower(str_replace(' ', '_', $value['menu']));
                $result_akses[$value['id_menu']] = $value;
            }

            $dataRoles = $this->checkDataRoles($userdata->nik);

            Session::put('user_id', $userdata->id);
            Session::put('group', $userdata->group_karyawan);
            Session::put('group_karyawan', $userdata->group_karyawan);
            Session::put('group_karyawan_name', $userdata->group_karyawan_name);
            Session::put('nama_lengkap', $userdata->nama_lengkap);
            Session::put('username', $userdata->username);
            Session::put('akses', $userdata->akses);
            Session::put('nik', $userdata->nik);
            Session::put('id_company', $userdata->company ?? '');
            Session::put('area_kerja', $userdata->nama_company ?? '');
            Session::put('id_karyawan', $userdata->id_karyawan ?? '0');
            Session::put('akses_menu', json_encode($result_akses));

            if (count($dataRoles) == 1) {
                return redirect('dashboard');
            }
            return redirect('roles');
        } else {
            return redirect()->action([LoginController::class, 'index'], ['error' => 'Pengguna Tidak Ditemukan']);
        }
    }

    public function submitNewPassword(Request $request)
    {
        $data = $request->all();
        $userdata = Users::where('id', session('user_id'))->first();

        if (!empty($userdata)) {
            $userdata->password = Hash::make($data['new_password']);
            $userdata->save();
            return redirect('dashboard');
        } else {
            return redirect()->action('web\auth\LoginController@changePassword', ['error' => 'Password not valid']);
        }
    }

    public function signInAuth(Request $request)
    {
        $data = $request->all();
        $credentials = $request->only('username', 'password');
        $result['is_valid'] = false;

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                $result['message'] = 'Pengguna Tidak Ditemukan';
                return response()->json($result);
            }
        } catch (JWTException $e) {
            $result['message'] = 'Pengguna Tidak Valid, Tidak Dapat Login.';
            return response()->json($result);
        }

        $userdata = DB::table('users as usr')
            ->select([
                'usr.*',
                'ha.group as akses',
                'kry.company',
                'ut.nama_company',
                'kry.nama_lengkap'
            ])
            ->join('karyawan as kry', 'kry.nik', 'usr.nik')
            ->join('company as ut', 'ut.id', 'kry.company')
            ->join('users_group as ha', 'ha.id', '=', 'usr.user_group')
            ->where(function ($query) use ($data) {
                return $query->where('usr.username', '=', $data['username'])
                    ->orWhere('usr.nik', '=', $data['username']);
            })
            ->whereNull('usr.deleted')
            ->first();

        if (!empty($userdata)) {
            /*update token fcm */
            $usersFcm = Users::find($userdata->id);
            $usersFcm->fcm_token = $data['tokenFcm'];
            $usersFcm->save();
            /*update token fcm */

            $result['is_valid'] = true;
            $result['message'] = 'Success';
            $result['data'] = $userdata;
            $result['token'] = $token;
        } else {
            $result['message'] = 'Pengguna Tidak Ditemukan di Database';
        }

        return response()->json($result);
    }

    public function refreshTokenFcm(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;

        /*update token fcm */
        $usersFcm = Users::find($data['users_id']);
        $usersFcm->fcm_token = $data['tokenFcm'];
        $usersFcm->save();
        /*update token fcm */

        $result['is_valid'] = true;
        $result['message'] = 'Success';
        $result['data'] = $usersFcm;
        $result['token'] = $data['tokenFcm'];

        return response()->json($result);
    }

    public function checkDataRoles($nik)
    {
        $data = Users::distinct()->select([
            'users.user_group'
        ])->where('users.nik', $nik)->whereNull('users.deleted')->get()->toArray();
        return $data;
    }

    public function signOut(Request $request)
    {
        session('user_id', '');
        session('username', '');
        session('group_karyawan', '');
        session('akses', '');
        session('token', '');
        session('nik', '');
        session('akses_menu', '');
        session('company', '');
        session('id_company', '');
        session('id_karyawan', '');
        Session::flush();
        return redirect('/')->with('success', 'Berhasil Keluar');
    }

    public function getAuthenticatedUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'token_expired' => $e->getMessage()
            ]);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'token_invalid' => $e->getMessage()
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'token_absent' => $e->getMessage()
            ]);
        }

        return response()->json(compact('user'));
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token', 500]);
        }

        return response()->json(compact('token'));
    }

    public function changeSession(Request $request)
    {
        $data = $request->all();

        Session::put('group', $data['group']);
        Session::put('group_karyawan', $data['group']);
        Session::put('group_karyawan_name', $data['group_name']);
        return response()->json(['message' => 'success', 'is_valid' => true]);
    }
}
