<?php

use App\Http\Controllers\api\Messaging\FcmController;
use App\Models\Master\Actor;
use App\Models\Master\DocumentTransaction;
use App\models\master\MasterDepartemen;
use App\Models\Master\PricePNBP;
use App\Models\Master\RoutingPermission;
use App\Models\Master\RoutingReminder;
use App\Models\Master\Users;
use App\Models\Master\UsersPermission;
use App\Models\Own\ProdukSatuan;
use App\Models\Transaksi\Forecast;
use App\Models\Transaksi\NotificationCenter;
use App\Models\Transaksi\Project;
use App\RequestCertificate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

function digit_count($length, $value)
{
    while (strlen($value) < $length)
        $value = '0' . $value;
    return $value;
}

function generateNoDocument()
{
    $no = 'DOC' . date('y') . strtoupper(date('M'));
    $data = DB::table('document')->where('no_document', 'LIKE', '%' . $no . '%')->orderBy('no_document', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->no_document);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}
function generateNoSertifikat()
{
    $no = 'DOC' . date('y') . strtoupper(date('M'));
    $data = DB::table('request_sertificate')->where('no_request', 'LIKE', '%' . $no . '%')->orderBy('no_request', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->no_request);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}
function generateCodeForecast()
{
    $no = 'FR' . date('y') . strtoupper(date('M'));
    $data = DB::table('forecast')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}

function getRomawiMonth($date = "")
{
    $month = $date == '' ? date('m') : date('m', strtotime($date));
    $month = intval($month);
    $romawi = array("", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII");
    return $romawi[$month];
}

function generateCodePO($forecast = '')
{
    $romawiMonth = getRomawiMonth();
    $year = date('Y');
    $forecastFind = Forecast::find($forecast);
    $no = '/PO/DPI/' . $romawiMonth . '/' . $year;
    if($forecastFind->company == 6){
        $no = '/PO/MNI/' . $romawiMonth . '/' . $year;
    }
    if($forecastFind->company == 7){
        $no = '/PO/BCI/' . $romawiMonth . '/' . $year;
    }
    $data = DB::table('purchase_order')->where('code', 'LIKE', '%' . $year . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(3, $seq);
    $no = $seq . $no;
    return $no;
}

function generateCodeSPK()
{
    $romawiMonth = getRomawiMonth();
    $year = date('Y');
    $no = '/SPK/DPI/' . $romawiMonth . '/' . $year;
    $data = DB::table('spk_jasa_instalasi')->where('code', 'LIKE', '%' . $year . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(3, $seq);
    $no = $seq . $no;
    return $no;
}

function generateCodeInvoice()
{
    $romawiMonth = getRomawiMonth();
    $year = date('Y');
    $no = '/DPI/' . $romawiMonth . '/' . $year;
    $data = DB::table('invoice')->where('code', 'LIKE', '%' . $year . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(3, $seq);
    $no = $seq . $no;
    return $no;
}

function generateCodeVisitDate()
{
    $no = 'VP' . date('y') . strtoupper(date('M'));
    $data = DB::table('visit_plan')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}

function generateCodeProject($codeCustomer = 'NAN', $customerId = 0)
{
    $totalProjectCustomer = Project::whereNull('deleted')->where('customer', $customerId)->count('id');
    $totalProjectCustomer += 1;
    $numberProject = $totalProjectCustomer < 10 ? '0' . $totalProjectCustomer : $totalProjectCustomer;

    $no = '/' . $codeCustomer . '/' . $numberProject . '/' . date('y') . '-' . strtoupper(date('m'));
    $data = DB::table('project')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no = $seq . $no;
    return $no;
}

function generateCodeReimburse()
{
    $no = 'R' . date('y') . strtoupper(date('M'));
    $data = DB::table('reimbursement')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}

function generateCodePresence()
{
    $no = 'PRS' . date('y') . strtoupper(date('M'));
    $data = DB::table('employee_presence')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}

function generateCodeBuget()
{
    $no = 'B' . date('y') . strtoupper(date('M'));
    $data = DB::table('budgeting')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}

function generateCodeCustomer()
{
    $no = 'CUST' . date('y') . strtoupper(date('M'));
    $data = DB::table('customer')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}

function generateCodeLeave()
{
    $no = 'LV' . date('y') . strtoupper(date('M'));
    $data = DB::table('employee_leave')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}

function generateCodeTermIdBank()
{
    $no = 'BNK_' . date('y') . strtoupper(date('M'));
    $data = DB::table('dictionary')->where('term_id', 'LIKE', '%' . $no . '%')
        ->where('context', 'BNK')
        ->orderBy('term_id', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->term_id);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}

function generateCodeTermIdGroup()
{
    $no = 'GROUP_' . date('y') . strtoupper(date('M'));
    $data = DB::table('dictionary')->where('term_id', 'LIKE', '%' . $no . '%')
        ->where('context', 'GROUP')
        ->orderBy('term_id', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->term_id);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}

function generateCodePerdin()
{
    $no = 'PD' . date('y') . strtoupper(date('M'));
    $data = DB::table('official_travel')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}

function generateCodePerdinAfterSales()
{
    $no = 'PDAS' . date('y') . strtoupper(date('M'));
    $data = DB::table('official_travel')->where('type', 'AFTER_SALES')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}

function generateCodePerdinVisit()
{
    $no = 'PV' . date('y') . strtoupper(date('M'));
    $data = DB::table('official_travel_visiting')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}

function generateCodePerdinRealisation()
{
    $no = 'PDR' . date('y') . strtoupper(date('M'));
    $data = DB::table('official_travel_realisation')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}

function generateCodePerdinReturn()
{
    $no = 'RPD' . date('y') . strtoupper(date('M'));
    $data = DB::table('official_travel_return')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}

function generateCodeProduct()
{
    $no = 'P' . date('y') . strtoupper(date('M'));
    $data = DB::table('product')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}

function generateCodeRegion()
{
    $no = 'R' . date('y') . strtoupper(date('M'));
    $data = DB::table('region')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}

function generateNoQuotation()
{
    $no = 'QUO' . date('y') . strtoupper(date('m')) . date('d');
    $data = DB::table('quotation')->where('no_quotation', 'LIKE', '%' . $no . '%')->orderBy('no_quotation', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->no_quotation);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(3, $seq);
    $no .= $seq;
    return $no;
}

function generateNoSi()
{
    $no = 'SI' . date('y') . strtoupper(date('m'));
    $data = DB::table('shipping_instruction')->where('no_shipping', 'LIKE', '%' . $no . '%')->orderBy('no_shipping', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->no_shipping);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(3, $seq);
    $no .= $seq;
    return $no;
}

function generateNoJob()
{
    $no = 'JOB' . date('y') . strtoupper(date('m'));
    $data = DB::table('shipping_excecution')->where('job_number', 'LIKE', '%' . $no . '%')->orderBy('job_number', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->job_number);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(3, $seq);
    $no .= $seq;
    return $no;
}

function generateNoInvoice()
{
    $no = 'INV' . date('y') . strtoupper(date('m'));
    $data = DB::table('invoice')->where('no_invoice', 'LIKE', '%' . $no . '%')->orderBy('no_invoice', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->no_invoice);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(3, $seq);
    $no .= $seq;
    return $no;
}

function generateNoManifest()
{
    $no = 'MAN' . date('y') . strtoupper(date('m'));
    $data = DB::table('manifest')->where('no_manifest', 'LIKE', '%' . $no . '%')->orderBy('no_manifest', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->no_manifest);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(3, $seq);
    $no .= $seq;
    return $no;
}

function generateNoInvoicing()
{
    $no = 'INV' . strtoupper(date('m')) . date('y');
    $data = DB::table('invoicing')->where('no_invoice', 'LIKE', '%' . $no . '%')->orderBy('no_invoice', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->no_invoice);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    // dd($no);
    return $no;
}

function cekStatusRequest($id_req)
{
    $data_status = [];
    $data_req = RequestCertificate::with(['RequestContract'])->find($id_req);

    foreach ($data_req->RequestContract as $key => $value) {
        $data_status[] = $value->status;
    }

    $statusCount = [];
    foreach ($data_status as $status) {
        $statusCount[$status] = isset($statusCount[$status]) ? $statusCount[$status] + 1 : 1;
    }
    // dd($data_status);
    // Check for DRAFT status and != DRAFT
    if (in_array('DRAFT', $data_status) && array_diff($data_status, ['DRAFT'])) {
        return "ON PROCESS";
    }
    // jika masih ada status APPROVE maka buat APPROVE saja
    if (in_array('APPROVE', $data_status)) {
        return "ON PROCESS";
    }
    // jika masih ada status DONE maka buat DONE saja
    if (in_array('DONE', $data_status)) {
        return "DONE";
    }
    // jika masih ada status COMPLETE maka buat COMPLETE saja
    if (in_array('COMPLETE', $data_status)) {
        return "COMPLETE";
    }

    $mostFrequentStatus = '';
    $maxCount = 0;
    foreach ($statusCount as $status => $count) {
        if ($count > $maxCount) {
            $maxCount = $count;
            $mostFrequentStatus = $status;
        }
    }
    // dd($statusCount);
    // Periksa apakah semua jumlah status sama
    $uniqueCounts = array_unique(array_values($statusCount));
    // dd($mostFrequentStatus);
    if (count($uniqueCounts) == 1) {
        return $mostFrequentStatus;
    }

    // dd($mostFrequentStatus);
    return $mostFrequentStatus;
}

function generateNoPayment()
{
    $no = 'PAY' . strtoupper(date('m')) . date('y');
    $data = DB::table('payment_invoice')->where('no_payment', 'LIKE', '%' . $no . '%')->orderBy('no_payment', 'desc')->get()->toArray();

    $seq = 1;
    if (!empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->no_payment);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;
    return $no;
}


function sendFonteNotification($phoneNumber, $message)
{
    $client = new Client();
    $apiKey = env('FONTE_API_KEY');
    // dd($apiKey);
    try {
        $response = $client->post('https://api.fonnte.com/send', [
            'headers' => [
                'Authorization' => $apiKey,
            ],
            'form_params' => [
                'target' => $phoneNumber,
                'message' => $message,
                'countryCode' => '62',
            ]
        ]);

        $status = json_decode($response->getBody(), true);
        Log::info('Fonnte API Response: ' . json_encode($status));

        if (!$status['status']) {
            throw new \Exception('Failed to send WhatsApp message.');
        }
    } catch (\Exception $e) {
        throw new \Exception('Fonnte API error: ' . $e->getMessage());
    }
}


function cari_biaya_barang($nilai_barang)
{
    // Contoh data array yang diberikan
    $data_biaya = PricePNBP::get()->toArray();


    // Lakukan pencarian
    foreach ($data_biaya as $row) {
        if ($nilai_barang >= $row['batas_bawah'] && $nilai_barang <= $row['batas_atas']) {
            return $row['biaya'];
        }
    }

    // Jika tidak ditemukan data yang cocok
    return "Nilai barang tidak ditemukan dalam rentang yang ada.";
}

function getEmployee($user_id = 0)
{
    $kry = Users::where('users.id', $user_id)
        ->select(['k.*'])
        ->join('karyawan as k', 'k.nik', 'users.nik')
        ->whereNull('k.deleted')
        ->first();

    return $kry;
}

function routingCreate($menu = 0, $prevState = null, $group = null, $from_id = 0)
{
    $data = RoutingPermission::where('routing_permission.menu', $menu)
        ->where(
            'routing_permission.prev_state',
            $prevState
        )
        ->where(
            'routing_permission.group',
            $group
        )
        ->select(['routing_permission.*', 'm.nama as nama_menu'])
        ->join('menu as m', 'm.id', 'routing_permission.menu')
        ->whereNull('routing_permission.deleted')
        ->where('routing_permission.is_active', '1');
    if ($from_id != 0) {
        $data = $data->where('routing_permission.routing_header', $from_id);
    }
    $data = $data->first();

    if (!empty($data)) {
        $apiFcm = new FcmController();
        $params['user_id'] = $data->users;
        $params['title'] = 'Informasi';
        $params['body'] = 'Terdapat Pemberitahuan Approval Module ' . $data->nama_menu . ' Silakan Buka dan Approval Melalui Web';
        $fcm = $apiFcm->sendFcmNotificationSystem($params);
    }
    return $data;
}

function routingAcc($users = 0, $menu = 0, $prev_step = '', $group = null, $from_id = 0)
{
    $data = RoutingPermission::where('routing_permission.menu', $menu)
        ->select(['routing_permission.*', 'm.nama as nama_menu'])
        ->join('menu as m', 'm.id', 'routing_permission.menu')
        ->where('routing_permission.prev_state', $prev_step)
        ->where(
            'routing_permission.group',
            $group
        )
        ->whereNull('routing_permission.deleted')
        ->where('routing_permission.is_active', '1')
        ->orderBy('routing_permission.state', 'asc');
    if ($from_id != 0) {
        $data->where('routing_permission.routing_header', $from_id);
    }
    $data = $data->first();

    if (!empty($data)) {
        $apiFcm = new FcmController();
        $params['user_id'] = $data->users;
        $params['title'] = 'Informasi';
        $params['body'] = 'Terdapat Pemberitahuan Approval Module ' . $data->nama_menu . ' Silakan Buka dan Approval Melalui Web';
        $apiFcm->sendFcmNotificationSystem($params);
    }
    return $data;
}

function checkIsLastRouting($users = 0, $menu = 0, $prev_step = '', $group = null, $from_id = 0)
{
    $data = RoutingPermission::where('routing_permission.menu', $menu)
        ->select(['routing_permission.*', 'm.nama as nama_menu'])
        ->where(
            'routing_permission.group',
            $group
        )
        ->join('menu as m', 'm.id', 'routing_permission.menu')
        ->whereNull('routing_permission.deleted')
        ->where('routing_permission.is_active', '1')
        ->orderBy('routing_permission.state', 'desc');
    if ($from_id != 0) {
        $data->where('routing_permission.routing_header', $from_id);
    }
    $data = $data->first();

    if (!empty($data)) {
        if ($data->prev_state == $prev_step) {
            return true;
        }
        return false;
    }

    return false;
}

function createLogTransaction($users = 0, $code = '0', $desc = '', $remarks = null, $state = null)
{
    $actor = new Actor();
    $actor->users = $users;
    $actor->content = $desc;
    $actor->action = $desc;
    $actor->save();
    $actorId = $actor->id;

    $log = new DocumentTransaction();
    $log->actors = $actorId;
    $log->no_document = $code;
    $log->remarks = $remarks;
    $log->state = $state;
    $log->save();
}

function routingReminder($users = 0, $menu = 0, $code = "", $state = "COMPLETED", $primary = 0, $remarks = "", $from_id = 0)
{
    $routingReminder = RoutingReminder::whereNull('routing_reminder.deleted')
        ->select(['routing_reminder.*', 'm.nama as nama_menu'])
        ->join('menu as m', 'm.id', 'routing_reminder.menu')
        ->where('routing_reminder.menu', $menu);

    $routingReminder = $routingReminder->get()->toArray();
    // echo '<pre>';
    // print_r($routingReminder);die;

    $resultRoutingMessage['is_valid'] = true;
    NotificationCenter::where('notification_center.menu', $menu)->where('no_document', $code)
        ->where('notification_center.primary_key', $primary)
        ->whereNull('notification_center.read_date')
        ->delete();
    $remindersSave = [];
    foreach ($routingReminder as $key => $value) {
        $notificationCenter = new NotificationCenter();
        $notificationCenter->menu = $menu;
        $notificationCenter->no_document = $code;
        $notificationCenter->primary_key = $primary;
        $notificationCenter->state = $state;
        $notificationCenter->creator = $users;
        $notificationCenter->to_users = $value['users'];
        $notificationCenter->remarks = $remarks;
        $notificationCenter->redirect_link = $remarks . '/detail?id=' . $primary;
        $notificationCenter->save();
        $remindersSave[] = $notificationCenter->id;

        try {
            $apiFcm = new FcmController();
            $params['user_id'] = $value['users'];
            $params['title'] = 'Informasi';
            $params['body'] = 'Terdapat Pemberitahuan Reminder Module ' . $value['nama_menu'] . ' Silakan Buka dan Reminder Melalui Web';
            $resultRoutingMessage['fcm_result'] = $apiFcm->sendFcmNotificationSystem($params);
        } catch (\Throwable $th) {
            $resultRoutingMessage['fcm_result'] = $th->getMessage();
        }
    }

    $resultRoutingMessage['notif_center_ids'] = $remindersSave;

    return $resultRoutingMessage;
}

function reminderToCreatorTransaction($users = 0, $menu = 0, $code = "", $state = "COMPLETED", $primary = 0, $remarks = "", $to_users = 0)
{
    NotificationCenter::where('notification_center.menu', $menu)->where('no_document', $code)
        ->where('notification_center.primary_key', $primary)
        ->select(['notification_center.*', 'm.nama as nama_menu'])
        ->join('menu as m', 'm.id', 'notification_center.menu')
        ->whereNull('notification_center.read_date')
        ->where('notification_center.to_users', $users)
        ->delete();


    $notificationCenter = new NotificationCenter();
    $notificationCenter->menu = $menu;
    $notificationCenter->no_document = $code;
    $notificationCenter->primary_key = $primary;
    $notificationCenter->state = $state;
    $notificationCenter->creator = $users;
    $notificationCenter->to_users = $to_users;
    $notificationCenter->remarks = $remarks;
    $notificationCenter->redirect_link = $remarks . '/ubah?id=' . $primary;
    $notificationCenter->save();

    $apiFcm = new FcmController();
    $params['user_id'] = $to_users;
    $params['title'] = 'Informasi';
    $params['body'] = 'Terdapat Pemberitahuan Reminder Module ' . $remarks . ' Silakan Buka dan Reminder Melalui Web';
    $apiFcm->sendFcmNotificationSystem($params);
}

function reminderToRolesTransaction($users = 0, $menu = 0, $code = "", $state = "UPDATED", $primary = 0, $remarks = "", $group = "")
{
    if ($group != '') {
        $dataGroup = Users::whereNull('users.deleted')
            ->join('users_group as ug', 'ug.id', 'users.user_group')
            ->where('ug.group', $group)
            ->select(['users.*'])
            ->get()->toArray();

        $idUsers = collect($dataGroup)->pluck('id')->toArray();
        NotificationCenter::where('notification_center.menu', $menu)->where('no_document', $code)
            ->where('notification_center.primary_key', $primary)
            ->select(['notification_center.*', 'm.nama as nama_menu'])
            ->join('menu as m', 'm.id', 'notification_center.menu')
            ->whereNull('notification_center.read_date')
            ->whereIn('notification_center.to_users', $idUsers)
            ->delete();


        foreach ($idUsers as $key => $value) {
            $to_users = $value;
            $notificationCenter = new NotificationCenter();
            $notificationCenter->menu = $menu;
            $notificationCenter->no_document = $code;
            $notificationCenter->primary_key = $primary;
            $notificationCenter->state = $state;
            $notificationCenter->creator = $users;
            $notificationCenter->to_users = $to_users;
            $notificationCenter->remarks = $remarks;
            $notificationCenter->redirect_link = $remarks . '/detail?id=' . $primary . '&backto=dashboard';
            $notificationCenter->save();

            $apiFcm = new FcmController();
            $params['user_id'] = $to_users;
            $params['title'] = 'Informasi';
            $params['body'] = 'Terdapat Pemberitahuan Reminder Module ' . $remarks . ' Silakan Buka dan Reminder Melalui Web';
            $apiFcm->sendFcmNotificationSystem($params);
        }
    }
}

function updateReadNotification($menu = 0, $primary = 0)
{
    NotificationCenter::where('menu', $menu)
        ->where('primary_key', $primary)
        ->whereNull('read_date')
        ->where('to_users', session('user_id'))
        ->update(['read_date' => date('Y-m-d H:i:s')]);
}

function setSessionUserFromApp($user_id = 0)
{
    $userdata = DB::table('users as usr')
        ->select([
            'usr.*',
            'ha.group as akses',
            'kry.company',
            'ut.nama_company',
            'kry.nama_lengkap',
            'kry.group as group_karyawan',
            'dic.keterangan as group_karyawan_name'
        ])
        ->join('karyawan as kry', 'kry.nik', 'usr.nik')
        ->join('company as ut', 'ut.id', 'kry.company')
        ->join('users_group as ha', 'ha.id', '=', 'usr.user_group')
        ->leftJoin('dictionary as dic', 'dic.term_id', '=', 'kry.group')
        ->where('usr.id', $user_id)
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
        Session::put('akses_menu', json_encode($result_akses));
    }
}


function terbilang($angka)
{
    $huruf = array(
        '',
        'satu',
        'dua',
        'tiga',
        'empat',
        'lima',
        'enam',
        'tujuh',
        'delapan',
        'sembilan',
        'sepuluh',
        'sebelas',
        'dua belas',
        'tiga belas',
        'empat belas',
        'lima belas',
        'enam belas',
        'tujuh belas',
        'delapan belas',
        'sembilan belas'
    );

    if ($angka < 20) {
        return $huruf[$angka];
    } elseif ($angka < 100) {
        return terbilang(floor($angka / 10)) . ' puluh ' . terbilang($angka % 10);
    } elseif ($angka < 200) {
        return 'seratus ' . terbilang($angka - 100);
    } elseif ($angka < 1000) {
        return terbilang(floor($angka / 100)) . ' ratus ' . terbilang($angka % 100);
    } elseif ($angka < 1000000) {
        return terbilang(floor($angka / 1000)) . ' ribu ' . terbilang($angka % 1000);
    } elseif ($angka < 1000000000) {
        return terbilang(floor($angka / 1000000)) . ' juta ' . terbilang($angka % 1000000);
    } elseif ($angka < 1000000000000) {
        return terbilang(floor($angka / 1000000000)) . ' milyar ' . terbilang($angka % 1000000000);
    } elseif ($angka < 1000000000000000) {
        return terbilang(floor($angka / 1000000000000)) . ' triliun ' . terbilang($angka % 1000000000000);
    }
}
