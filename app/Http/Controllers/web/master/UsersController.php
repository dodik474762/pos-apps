<?php

namespace App\Http\Controllers\web\master;

use App\Http\Controllers\api\master\UsersController as MasterUsersController;
use App\Http\Controllers\Controller;
use App\Models\Master\Branch;
use App\Models\Master\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsersController extends Controller
{
    public $akses_menu = [];
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->akses_menu = json_decode(session('akses_menu'));
    }

    public function getHeaderCss()
    {
        return array(
            'js-1' => asset('assets/js/controllers/master/users.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        );
    }

    public function getTitleParent()
    {
        return "Master";
    }

    public function getTableName()
    {
        return "";
    }

    public function getTitle()
    {
        return "Users";
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $view = view('web.users.index', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function add()
    {
        $data['data'] = [];
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $company_id = session('id_company');
        $data['data_roles'] = Roles::whereNull('deleted')->where('group', '!=', 'SUPERADMIN')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('company_id', $company_id))
            ->get()->toArray();
        $data['data_branch'] = Branch::whereNull('deleted')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('company_id', $company_id))
            ->get();
        $view = view('web.users.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function ubah(Request $request)
    {
        $api = new MasterUsersController();
        $data = $request->all();
        $data['data'] = $api->getDetailData($data['id'])->original;
        $company_id = session('id_company');
        $data['data_roles'] = Roles::whereNull('deleted')->where('group', '!=', 'SUPERADMIN')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('company_id', $company_id))
            ->get()->toArray();
        $data['title'] = 'Form ' . $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['data_branch'] = Branch::whereNull('deleted')
            ->when($company_id !== null && $company_id !== '', fn ($q) => $q->where('company_id', $company_id))
            ->get();
        $view = view('web.users.formadd', $data);
        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Form ' . $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();
        return view('web.template.main', $put);
    }

    public function changePassword()
    {
        if (!session('user_id')) {
            return redirect('/');
        }

        $data['title'] = 'Change Password';
        $data['title_parent'] = 'Settings';
        $view = view('web.users.changePassword', $data);
        $put['title_content'] = 'Change Password';
        $put['title_top'] = 'Change Password';
        $put['title_parent'] = 'Settings';
        $put['view_file'] = $view;
        return view('web.template.main', $put);
    }

    public function submitChangePassword(Request $request)
    {
        if (!session('user_id')) {
            return redirect('/');
        }

        $data = $request->all();
        $result['is_valid'] = false;

        $validator = Validator::make($data, [
            'new_password' => 'required|string|min:8|max:255|confirmed',
        ], [
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.max' => 'Password baru maksimal 255 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                $result['message'] = $validator->errors()->first();
                return response()->json($result);
            }

            return redirect()->route('user.changePassword')->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $user = User::whereNull('deleted')->where('id', session('user_id'))->first();

            if (!$user) {
                throw new \Exception('User tidak ditemukan.');
            }

            $user->password = $data['new_password'];
            $user->save();

            $token = JWTAuth::fromUser($user);

            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Password berhasil diubah. Silakan login kembali dengan password baru.';
            $result['token'] = $token;
        } catch (\Throwable $th) {
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        if ($result['is_valid']) {
            session()->flush();
            return redirect('/?success='.urlencode($result['message']));
        }

        return redirect()->route('user.changePassword')->with('error', $result['message']);
    }
}
