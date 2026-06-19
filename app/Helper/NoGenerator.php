<?php

use App\Http\Controllers\api\Messaging\FcmController;
use App\Models\Master\AccountMapping;
use App\Models\Master\Actor;
use App\Models\Master\Currency;
use App\Models\Master\Customer;
use App\Models\Master\DocumentTransaction;
use App\Models\Master\PricePNBP;
use App\Models\Master\ProductUom;
use App\Models\Master\ProductUomPrice;
use App\Models\Master\RoutingPermission;
use App\Models\Master\RoutingReminder;
use App\Models\Master\Users;
use App\Models\Master\UsersPermission;
use App\Models\Transaction\GeneralLedger;
use App\Models\Transaction\ProductUomCost;
use App\Models\Transaction\SalesInvoiceDtl;
use App\Models\Transaction\SalesOrderDetail;
use App\Models\Transaction\SalesReturnDtl;
use App\Models\Transaction\SalesReturnHdr;
use App\Models\Transaksi\NotificationCenter;
use App\RequestCertificate;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

function digit_count($length, $value)
{
    while (strlen($value) < $length) {
        $value = '0' . $value;
    }

    return $value;
}

function generateNoDocument()
{
    $no = 'DOC' . date('y') . strtoupper(date('M'));
    $data = DB::table('document')->where('no_document', 'LIKE', '%' . $no . '%')->orderBy('no_document', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->no_document);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    return $no;
}

function getRomawiMonth($date = '')
{
    $month = $date == '' ? date('m') : date('m', strtotime($date));
    $month = intval($month);
    $romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

    return $romawi[$month];
}

function generateCodeProduct()
{
    $no = 'PROD-' . strtoupper(date('m')) . date('y');
    $data = DB::table('product')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generateCodeCustomer()
{
    $no = 'CUST' . strtoupper(date('m')) . date('y');
    $data = DB::table('customer')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generateVPN()
{
    $no = 'VP' . strtoupper(date('m')) . date('y');
    $data = DB::table('vendor_payment_header')->where('payment_number', 'LIKE', '%' . $no . '%')->orderBy('payment_number', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->payment_number);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generatePRN()
{
    $no = 'RE-' . strtoupper(date('m')) . date('y');
    $data = DB::table('purchase_return')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generateCodePresence()
{
    $no = 'AB-' . strtoupper(date('m')) . date('y');
    $data = DB::table('purchase_return')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generateNoPO()
{
    $no = 'PO' . strtoupper(date('m')) . date('y');
    $data = DB::table('purchase_order')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generateNoSO()
{
    $no = 'SO' . strtoupper(date('m')) . date('y');
    $data = DB::table('sales_order_headers')->where('so_number', 'LIKE', '%' . $no . '%')->orderBy('so_number', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->so_number);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generateNoAdjStock()
{
    $no = 'ADJ' . strtoupper(date('m')) . date('y');
    $data = DB::table('product_adjustment_stock_header')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generateNoDO()
{
    $no = 'DO' . strtoupper(date('m')) . date('y');
    $data = DB::table('delivery_order_header')->where('do_number', 'LIKE', '%' . $no . '%')->orderBy('do_number', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->do_number);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generateNoSI()
{
    $no = 'SI' . strtoupper(date('m')) . date('y');
    $data = DB::table('sales_invoice_header')->where('invoice_number', 'LIKE', '%' . $no . '%')->orderBy('invoice_number', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->invoice_number);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generateNoSP()
{
    $no = 'SP' . strtoupper(date('m')) . date('y');
    $data = DB::table('sales_payment_header')->where('payment_code', 'LIKE', '%' . $no . '%')->orderBy('payment_code', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->payment_code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generateNoPL()
{
    $no = 'PL' . strtoupper(date('m')) . date('y');
    $data = DB::table('packing_list')->where('packing_list_no', 'LIKE', '%' . $no . '%')->orderBy('packing_list_no', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->packing_list_no);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generateNoReceivedCashier()
{
    $no = 'RCC' . strtoupper(date('m')) . date('y');
    $data = DB::table('receive_payment_header')->where('code', 'LIKE', '%' . $no . '%')->orderBy('code', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generateNoReturn()
{
    $no = 'SR' . strtoupper(date('m')) . date('y');
    $data = DB::table('sales_return')->where('return_number', 'LIKE', '%' . $no . '%')->orderBy('return_number', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->return_number);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generateNoCN()
{
    $no = 'CN' . strtoupper(date('m')) . date('y');
    $data = DB::table('credit_note')->where('credit_note_number', 'LIKE', '%' . $no . '%')->orderBy('credit_note_number', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->credit_note_number);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generateNoReturOther()
{
    $no = 'RH' . strtoupper(date('m')) . date('y');
    $data = DB::table('sales_retur_of_consigment')->where('return_number', 'LIKE', '%' . $no . '%')->orderBy('return_number', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->return_number);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generateNoRoutePlan()
{
    $no = 'RP' . strtoupper(date('m')) . date('y');
    $data = DB::table('sales_plan_header')->where('plan_code', 'LIKE', '%' . $no . '%')->orderBy('plan_code', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->plan_code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generatePlanCode()
{
    $no = 'SO' . strtoupper(date('m')) . date('y');
    $data = DB::table('sales_plan_header')->where('plan_code', 'LIKE', '%' . $no . '%')->orderBy('plan_code', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->plan_code);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generateGrNumber()
{
    $no = 'GR' . strtoupper(date('m')) . date('y');
    $data = DB::table('goods_receipt_header')->where('gr_number', 'LIKE', '%' . $no . '%')->orderBy('gr_number', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->gr_number);
        $seq = intval($seq) + 1;
    }

    $seq = digit_count(4, $seq);
    $no .= $seq;

    // dd($no);
    return $no;
}

function generatePINumber()
{
    $no = 'PI' . strtoupper(date('m')) . date('y');
    $data = DB::table('purchase_invoice_header')->where('invoice_number', 'LIKE', '%' . $no . '%')->orderBy('invoice_number', 'desc')->get()->toArray();

    $seq = 1;
    if (! empty($data)) {
        $data = current($data);
        $seq = str_replace($no, '', $data->invoice_number);
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
        return 'ON PROCESS';
    }
    // jika masih ada status APPROVE maka buat APPROVE saja
    if (in_array('APPROVE', $data_status)) {
        return 'ON PROCESS';
    }
    // jika masih ada status DONE maka buat DONE saja
    if (in_array('DONE', $data_status)) {
        return 'DONE';
    }
    // jika masih ada status COMPLETE maka buat COMPLETE saja
    if (in_array('COMPLETE', $data_status)) {
        return 'COMPLETE';
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
    if (! empty($data)) {
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
    $client = new Client;
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
            ],
        ]);

        $status = json_decode($response->getBody(), true);
        Log::info('Fonnte API Response: ' . json_encode($status));

        if (! $status['status']) {
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
    return 'Nilai barang tidak ditemukan dalam rentang yang ada.';
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

    if (! empty($data)) {
        $apiFcm = new FcmController;
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

    if (! empty($data)) {
        $apiFcm = new FcmController;
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

    if (! empty($data)) {
        if ($data->prev_state == $prev_step) {
            return true;
        }

        return false;
    }

    return false;
}

function createLogTransaction($users = 0, $code = '0', $desc = '', $remarks = null, $state = null)
{
    $actor = new Actor;
    $actor->users = $users;
    $actor->content = $desc;
    $actor->action = $desc;
    $actor->save();
    $actorId = $actor->id;

    $log = new DocumentTransaction;
    $log->actors = $actorId;
    $log->no_document = $code;
    $log->remarks = $remarks;
    $log->state = $state;
    $log->save();
}

function routingReminder($users = 0, $menu = 0, $code = '', $state = 'COMPLETED', $primary = 0, $remarks = '', $from_id = 0)
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
        $notificationCenter = new NotificationCenter;
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
            $apiFcm = new FcmController;
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

function reminderToCreatorTransaction($users = 0, $menu = 0, $code = '', $state = 'COMPLETED', $primary = 0, $remarks = '', $to_users = 0)
{
    NotificationCenter::where('notification_center.menu', $menu)->where('no_document', $code)
        ->where('notification_center.primary_key', $primary)
        ->select(['notification_center.*', 'm.nama as nama_menu'])
        ->join('menu as m', 'm.id', 'notification_center.menu')
        ->whereNull('notification_center.read_date')
        ->where('notification_center.to_users', $users)
        ->delete();

    $notificationCenter = new NotificationCenter;
    $notificationCenter->menu = $menu;
    $notificationCenter->no_document = $code;
    $notificationCenter->primary_key = $primary;
    $notificationCenter->state = $state;
    $notificationCenter->creator = $users;
    $notificationCenter->to_users = $to_users;
    $notificationCenter->remarks = $remarks;
    $notificationCenter->redirect_link = $remarks . '/ubah?id=' . $primary;
    $notificationCenter->save();

    $apiFcm = new FcmController;
    $params['user_id'] = $to_users;
    $params['title'] = 'Informasi';
    $params['body'] = 'Terdapat Pemberitahuan Reminder Module ' . $remarks . ' Silakan Buka dan Reminder Melalui Web';
    $apiFcm->sendFcmNotificationSystem($params);
}

function reminderToRolesTransaction($users = 0, $menu = 0, $code = '', $state = 'UPDATED', $primary = 0, $remarks = '', $group = '')
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
            $notificationCenter = new NotificationCenter;
            $notificationCenter->menu = $menu;
            $notificationCenter->no_document = $code;
            $notificationCenter->primary_key = $primary;
            $notificationCenter->state = $state;
            $notificationCenter->creator = $users;
            $notificationCenter->to_users = $to_users;
            $notificationCenter->remarks = $remarks;
            $notificationCenter->redirect_link = $remarks . '/detail?id=' . $primary . '&backto=dashboard';
            $notificationCenter->save();

            $apiFcm = new FcmController;
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
            'dic.keterangan as group_karyawan_name',
        ])
        ->join('karyawan as kry', 'kry.nik', 'usr.nik')
        ->join('company as ut', 'ut.id', 'kry.company')
        ->join('users_group as ha', 'ha.id', '=', 'usr.user_group')
        ->leftJoin('dictionary as dic', 'dic.term_id', '=', 'kry.group')
        ->where('usr.id', $user_id)
        ->whereNull('usr.deleted')
        ->first();

    if (! empty($userdata)) {
        $dataMenu = UsersPermission::where('users_permissions.users_group', $userdata->user_group)
            ->select([
                'users_permissions.*',
                'am.nama as menu',
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
    $huruf = [
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
        'sembilan belas',
    ];

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

function postingGL($reference = '', $account_id = 0, $account_name = '', $dc = '', $amount = 0, $currency = 1, $desc = '', $user_id = '')
{
    $postingDate = now();

    $exist = GeneralLedger::where('reference', $reference)->where('account_id', $account_id)
        ->where('dc', $dc)->first();

    $post = empty($exist) ? new GeneralLedger : $exist;
    $post->posting_date = $postingDate;
    $post->reference = $reference;
    $post->account_id = $account_id;
    $post->account_name = $account_name;
    $post->dc = $dc;
    $post->amount = $amount;
    $post->currency = $currency;
    $post->description = $desc;
    $post->created_by = $user_id == '' ? session('user_id') : $user_id;
    $post->save();
}

function cancelGL($reference = '', $account_id = 0, $account_name = '', $dc = '', $amount = 0, $currency = 1, $desc = '')
{
    GeneralLedger::where('reference', $reference)->where('account_id', $account_id)
        ->where('dc', $dc)->delete();
}

function cancelAllGL($reference = '')
{
    GeneralLedger::where('reference', $reference)->delete();
}

function updateAllGL($reference = '', $to_reference = '')
{
    GeneralLedger::where('reference', $to_reference)->update(['reference' => $reference]);
}

function getGeneralLedger($reference = '')
{
    $general_ledgers = DB::table('general_ledgers as gl')
        ->join('coa as c', 'c.id', '=', 'gl.account_id')
        ->select('gl.*', 'c.account_code', 'c.account_name as account_name_coa')
        ->where('gl.reference', 'like', "%{$reference}%")
        ->orderBy('gl.posting_date')
        ->orderBy('gl.id')
        ->get();

    return $general_ledgers;
}

function hitungKonversiKeSatuanTerkecil($product_uom, $unit, &$cache = [])
{
    if (isset($cache[$unit])) {
        return $cache[$unit];
    }

    $uom = $product_uom->firstWhere('unit_tujuan', $unit);

    if (!$uom) {
        return 0;
    }

    // Base case: satuan terkecil (tidak punya parent / level 1)
    if (!$uom->nilai_konversi_terkecil) {
        $cache[$unit] = 1;
        return 1;
    }

    // Recursive case: nilai_konversi (ke parent) dikali konversi parent ke satuan terkecil
    $nilaiKeParent = $uom->nilai_konversi_terkecil;
    $parentKeTerkecil = hitungKonversiKeSatuanTerkecil($product_uom, $uom->nilai_konversi_terkecil, $cache);

    $hasil = $nilaiKeParent * $parentKeTerkecil;
    $cache[$unit] = $hasil;

    return $hasil;
}

function konversiSatuan($productId, $unitDari, $unitKe, $qty = 1)
{
    $product_uom = ProductUom::whereNull('deleted')
        ->where('product', $productId)
        ->orderBy('level')
        ->get();

    $cache = [];

    $konversiDari = hitungKonversiKeSatuanTerkecil($product_uom, $unitDari, $cache);
    $konversiKe = hitungKonversiKeSatuanTerkecil($product_uom, $unitKe, $cache);

    if (!$konversiDari || !$konversiKe) {
        return null;
    }

    // qty dalam satuan terkecil, lalu dikonversi ke satuan tujuan
    return ($qty * $konversiDari) / $konversiKe;
}

function totalKeSatuanTerkecil($product_uom, array $inputs)
{
    $total = 0;

    foreach ($inputs as $input) {
        $uom = $product_uom->firstWhere('unit_tujuan', $input['unit']);

        if (!$uom) {
            continue;
        }

        $total += $input['qty'] * $uom->nilai_konversi_terkecil;
    }

    return $total;
}

function pecahDariSatuanTerkecil($product_uom, $totalTerkecil)
{
    // Urutkan dari konversi TERBESAR ke TERKECIL
    $uomUrut = $product_uom->sortByDesc('nilai_konversi_terkecil');

    $hasil = [];
    $sisa = $totalTerkecil;

    foreach ($uomUrut as $uom) {
        $konversi = $uom->nilai_konversi_terkecil;

        if (!$konversi) {
            continue;
        }

        $qtyUnitIni = intdiv((int) round($sisa), (int) $konversi);
        $hasil[$uom->unit_tujuan] = $qtyUnitIni;

        $sisa -= $qtyUnitIni * $konversi;
    }

    return $hasil;
}

function normalisasiQtyUom($productId, array $inputs)
{
    $product_uom = ProductUom::whereNull('deleted')
        ->where('product', $productId)
        ->orderBy('level')
        ->get();

    $totalTerkecil = totalKeSatuanTerkecil($product_uom, $inputs);

    return pecahDariSatuanTerkecil($product_uom, $totalTerkecil);
}

function hitungHargaPerSatuan($price, $product_uom, $unitTerbesar, $unitTujuan, &$cache = [])
{
    $konversiTerbesar = hitungKonversiKeSatuanTerkecil($product_uom, $unitTerbesar, $cache);
    $konversiTujuan = hitungKonversiKeSatuanTerkecil($product_uom, $unitTujuan, $cache);

    if (!$konversiTerbesar) {
        return 0;
    }

    return ($price / $konversiTerbesar) * $konversiTujuan;
}

function getSmallestUnit($productId, $fromUnitId, $qty = 1)
{
    $multiplier = 1;
    $currentUnit = $fromUnitId;

    while (true) {
        // Ambil konversi dari unit saat ini
        $conversion = DB::table('product_uom')
            ->where('product', $productId)
            ->where('unit_tujuan', $currentUnit)
            ->whereNull('deleted')
            ->first();

        if (! $conversion || $conversion->level == 1) {
            // tidak ada konversi lebih lanjut, unit saat ini = unit terkecil
            $baseUnit = $currentUnit;
            break;
        }

        // Kalikan nilai konversi
        $multiplier *= $conversion->nilai_konversi;

        // Lanjut ke level berikutnya
        $currentUnit = $conversion->unit_dasar;
    }

    return [
        'base_unit' => $baseUnit,
        'multiplier' => $multiplier,
        'qty_in_base_unit' => $qty * $multiplier,
    ];
}

function getHargaSemuaUnit($productId, $hargaUnit, $unitId)
{
    $result = [];

    // Simpan harga unit ini
    $unitName = DB::table('unit')->where('id', $unitId)->value('name');
    $result[] = [
        'unit_id'   => $unitId,
        'unit_name' => $unitName,
        'harga'     => $hargaUnit,
    ];

    // Cari konversi dari unit ini ke unit lebih kecil
    // unit_tujuan = unitId ini, unit_dasar = unit lebih kecil
    $conversion = DB::table('product_uom')
        ->where('product', $productId)
        ->where('unit_tujuan', $unitId)
        ->whereNull('deleted')
        ->first();

    if ($conversion && $conversion->level != 1) {
        // Harga unit lebih kecil = harga sekarang / nilai_konversi
        $hargaUnitLebihKecil = $hargaUnit / $conversion->nilai_konversi;

        // Rekursi ke unit lebih kecil
        $subResult = getHargaSemuaUnit(
            $productId,
            $hargaUnitLebihKecil,
            $conversion->unit_dasar
        );

        $result = array_merge($result, $subResult);
    }

    return $result;
}


function getSmallestUnitV2($productId, $fromUnitId, $qty = 1)
{
    // Ambil konversi dari unit saat ini
    $conversion = DB::table('product_uom')
        ->where('product', $productId)
        ->where('unit_tujuan', $fromUnitId)
        ->whereNull('deleted')
        ->first();

    return $conversion;
}



function getLargestUnit($productId, $fromUnitId, $qty = 1)
{
    // Ambil semua konversi unit untuk produk terkait
    $data_product_uom = DB::table('product_uom')
        ->where('product', $productId)
        ->whereNull('deleted')
        ->orderBy('level')
        ->get();
    // echo '<pre>';
    // print_r($data_product_uom);
    // die;

    if ($data_product_uom->isEmpty()) {
        return [
            'largest_unit' => $fromUnitId,
            'largest_unit_name' => '',
            'multiplier' => 1,
            'qty_in_largest_unit' => $qty,
        ];
    }

    // Cari baris dengan state = 'large' (unit terbesar)
    $largest = $data_product_uom->firstWhere('state', 'large');

    if (! $largest) {
        // Kalau tidak ada 'large', ambil level tertinggi
        $largest = $data_product_uom->sortByDesc('level')->first();
    }

    $largestUnit = $largest->unit_tujuan;

    // Hitung total multiplier dari unit kecil ke unit terbesar
    $multiplier = 1;
    foreach ($data_product_uom as $value) {
        $multiplier *= $value->nilai_konversi;
        if ($value->unit_tujuan == $largestUnit) {
            break;
        }
    }

    // Ambil nama unit terbesar (opsional)
    $unit_tujuan = DB::table('unit')->where('id', $largestUnit)->first();

    return [
        'largest_unit' => $largestUnit,
        'largest_unit_name' => $unit_tujuan->name ?? '',
        'multiplier' => $multiplier,
        'qty_in_largest_unit' => $qty / $multiplier,
    ];
}

function stockUpdate($reference_id = 0, $warehouse = 0, $product = 0, $baseUnit = 0, $convertedQty = 0, $value = [], $type = '', $move_type = '')
{
    // Update stok di gudang
    $warehouseId = $warehouse; // sesuaikan, atau ambil dari form GR

    $stock = DB::table('product_stock')
        ->where('product', $value['product'])
        ->where('unit', $baseUnit)
        ->where('warehouse', $warehouseId)
        ->first();

    $product_uom_large = ProductUom::whereNull('deleted')
        ->where('product', $value['product'])
        ->where('state', 'large')
        ->first();

    $sell_price     = 0;
    $purchase_price = 0;

    if ($product_uom_large) {
        $product_price = ProductUomPrice::where('product', $value['product'])
            ->where('unit', $product_uom_large->unit_tujuan)
            ->where('date_start', '<=', date('Y-m-d'))
            ->orderBy('id', 'desc')
            ->first();

        $product_cost = ProductUomCost::where('product', $value['product'])
            ->where('unit_id', $product_uom_large->unit_tujuan)
            ->where('date_start', '<=', date('Y-m-d'))
            ->where('is_active', '1')
            ->orderBy('id', 'desc')
            ->first();

        $sell_price     = $product_price->price  ?? 0;
        $purchase_price = $product_cost->cost    ?? 0;
    }

    if ($stock) {
        // Update qty existing
        DB::table('product_stock')
            ->where('id', $stock->id)
            ->update([
                'qty' => $type == 'add' ? $stock->qty + $convertedQty : $stock->qty - $convertedQty,
                'updated_at' => now(),
            ]);
    } else {
        // Insert baru
        DB::table('product_stock')->insert([
            'product' => $value['product'],
            'unit' => $baseUnit,
            'warehouse' => $warehouseId,
            'qty' => $type == 'add' ? $convertedQty : $convertedQty * -1,
            'avg_cost' => $value['price'] ?? 0,
            'created_at' => now(),
        ]);
    }

    DB::table('product_stock_move')->insert([
        'product' => $value['product'],
        'unit' => $baseUnit,
        'warehouse' => $warehouseId,
        'qty_in' => $type == 'add' ? $convertedQty : 0,
        'qty_out' => $type == 'add' ? 0 : $convertedQty,
        'move_type' => $move_type,
        'reference_id' => $reference_id,
        'price' => $sell_price,
        'purchase_price' => $purchase_price,
        'created_at' => now(),
    ]);
}

function stockRollback($reference_id = 0, $warehouse = 0, $product = 0, $baseUnit = 0, $convertedQty = 0, $value = [], $type = '')
{
    $warehouseId = $warehouse;

    // Ambil data stok
    $stock = DB::table('product_stock')
        ->where('product', $product)
        ->where('unit', $baseUnit)
        ->where('warehouse', $warehouseId)
        ->first();

    if ($stock) {
        // Rollback qty (kebalikan dari type)
        DB::table('product_stock')
            ->where('id', $stock->id)
            ->update([
                'qty' => $type == 'add'
                    ? $stock->qty - $convertedQty   // jika sebelumnya add, rollback = kurangi
                    : $stock->qty + $convertedQty,  // jika sebelumnya reduce, rollback = tambah
                'updated_at' => now(),
            ]);
    }

    // Catat pergerakan rollback di product_stock_move
    DB::table('product_stock_move')->insert([
        'product' => $product,
        'unit' => $baseUnit,
        'warehouse' => $warehouseId,
        'qty_in' => $type == 'add' ? 0 : $convertedQty,   // jika sebelumnya add, rollback = keluar
        'qty_out' => $type == 'add' ? $convertedQty : 0,  // jika sebelumnya reduce, rollback = masuk
        'move_type' => 'rollback',
        'reference_id' => $reference_id,
        'price' => $value['price'] ?? 0,
        'created_at' => now(),
    ]);
}

function checkCustomerCreditLimit($customer = 0)
{
    $datadb = Customer::where('id', $customer)->first();

    $credit_limit = 0;
    if ($datadb) {
        $credit_limit = $datadb->credit_limit;
    }

    if ($credit_limit == 0) {
        return [
            'status' => true,
            'message' => 'Customer tidak memiliki batas kredit',
        ];
    }

    //jika payment terms bukan CASH
    if ($datadb->payment_terms != 3) {
        $min_invoice = 0;
        if ($datadb) {
            $min_invoice = $datadb->min_invoice;
        }

        if ($min_invoice == 0) {
            return [
                'status' => true,
                'message' => 'Customer tidak memiliki batas minimal invoice',
            ];
        }

        //count invoice outstanding
        $countInvoiceOutstanding = DB::table('sales_invoice_header')
            // ->whereIn('status', ['DRAFT', 'POSTED', 'PARTIAL PAID'])
            ->whereIn('status', ['POSTED', 'PARTIAL PAID'])
            ->whereNull('deleted')
            ->where('customer_id', $customer)
            ->count();

        if ($countInvoiceOutstanding >= $min_invoice) {
            return [
                'status' => false,
                'message' => 'Customer telah mencapai batas minimal invoice yaitu : ' . $min_invoice . ' dengan total invoice outstanding sebesar : ' . $countInvoiceOutstanding,
            ];
        }
    }

    // cek piutang belum tertagih
    $totalOutstanding = DB::table('sales_invoice_header')
        ->whereIn('status', ['DRAFT', 'POSTED', 'PARTIAL PAID'])
        ->whereNull('deleted')
        ->where('customer_id', $customer)
        ->whereRaw('(total_amount - amount_paid) > 0')
        ->sum(DB::raw('total_amount - amount_paid'));


    if ($totalOutstanding >= $credit_limit) {
        return [
            'status' => false,
            'message' => 'Customer telah mencapai batas kredit maksimal yaitu : ' . $credit_limit . ' dengan total piutang belum tertagih sebesar : ' . $totalOutstanding,
        ];
    } else {
        return [
            'status' => true,
            'message' => 'Customer masih memiliki sisa batas kredit sebesar : ' . ($credit_limit - $totalOutstanding),
        ];
    }
}

// ====== HELPER: CREATE AUTO SALES RETURN ======
function createAutoReturn($invoiceId, $items, $returnType = 'REFUND', $userId, $customerId)
{
    $penjualanAcc = AccountMapping::where('module', 'SALES_RETURN')->where('account_type', 'penjualan barang')->with('account')->first();
    $ppnKeluaranAcc = AccountMapping::where('module', 'SALES_RETURN')->where('account_type', 'ppn keluaran')->with('account')->first();
    $discAcc = AccountMapping::where('module', 'SALES_RETURN')->where('account_type', 'diskon penjualan')->with('account')->first();
    $depositAcc = AccountMapping::where('module', 'SALES_RETURN')->where('account_type', 'deposit pelanggan')->with('account')->first();
    $piutangAcc = AccountMapping::where('module', 'SALES_RETURN')->where('account_type', 'piutang usaha')->with('account')->first();

    if (!$penjualanAcc || !$ppnKeluaranAcc || !$discAcc || !$depositAcc || !$piutangAcc) {
        throw new \Exception('Konfigurasi akun untuk Sales Return belum lengkap.');
    }

    $header = new SalesReturnHdr();
    $header->return_number     = generateNoReturn();
    $header->created_by        = $userId;
    $header->status            = 'POSTED'; // langsung posted karena dari mobile
    $header->return_date       = now()->format('Y-m-d');
    $header->customer_id       = $customerId;
    $header->return_type       = $returnType;
    $header->refund_amount     = 0;
    $header->deposit_amount    = 0;
    $header->total_return_value = 0;
    $header->reason            = 'Auto return dari delivery confirm';
    $header->types            = 'good';
    $header->invoice_id        = $invoiceId;
    $header->save();

    $hdrId     = $header->id;
    $reference = $header->return_number;

    $totalAmount = 0;
    $disc_total  = 0;
    $net_total   = 0;
    $tax_total   = 0;

    foreach ($items as $item) {
        $invDtl = SalesInvoiceDtl::find($item['invoice_detail_id']);
        if (empty($invDtl)) continue;

        $qtyReturn  = (float)$item['qty_return'];
        $unitPrice  = (float)$invDtl->price;
        $originalQty = (float)($invDtl->original_qty ?? $invDtl->qty);

        // Hitung disc & tax proporsional
        $discAmount = ($originalQty > 0)
            ? round($invDtl->discount / $originalQty * $qtyReturn)
            : 0;
        $taxAmount = !empty($invDtl->tax_rate)
            ? round(($unitPrice * $qtyReturn - $discAmount) * ($invDtl->tax_rate / 100))
            : (($originalQty > 0) ? round($invDtl->tax_amount / $originalQty * $qtyReturn) : 0);
        $subtotal = ($unitPrice * $qtyReturn) - $discAmount;
        //  + $taxAmount;

        $detail = new SalesReturnDtl();
        $detail->return_id         = $hdrId;
        $detail->product_id        = $invDtl->product_id;
        $detail->qty_return        = $qtyReturn;
        $detail->unit_price        = $unitPrice;
        $detail->discount_amount   = $discAmount;
        $detail->tax_amount        = $taxAmount;
        $detail->type_tax          = $invDtl->type_tax ?? 'include';
        $detail->tax_rate          = $invDtl->tax_rate ?? 0;
        $detail->tax               = $invDtl->tax ?? 0;
        $detail->invoice_detail_id = $invDtl->id;
        $detail->save();

        // Update return_qty di invoice detail
        $invDtl->return_qty = ($invDtl->return_qty ?? 0) + $qtyReturn;
        $invDtl->save();

        // Update stock
        $so_detail = SalesOrderDetail::find($invDtl->so_detail_id);
        if ($so_detail) {
            $qtyBaseUnit = getSmallestUnit($invDtl->product_id, $so_detail->unit, $qtyReturn);
            $productUomLevel1 = ProductUom::where('product', $invDtl->product_id)->where('level', '1')->first();
            if ($productUomLevel1) {
                stockUpdate($hdrId, $item['warehouse_id'], $invDtl->product_id, $productUomLevel1->unit_tujuan, $qtyBaseUnit['qty_in_base_unit'], $item, 'add', 'sales_return');
            }
        }

        $disc_total  += $discAmount;
        $tax_total   += $taxAmount;
        $totalAmount += ($unitPrice * $qtyReturn);
        $net_total   += $subtotal;
    }

    // Update header total
    $header->total_return_value = $net_total;
    $header->deposit_amount     = $returnType == 'DEPOSIT' ? $net_total : 0;
    $header->refund_amount      = $returnType == 'REFUND' ? $net_total : 0;
    $header->save();

    // Posting GL
    $currency   = Currency::where('code', 'IDR')->first();
    $currencyId = $currency->id;

    postingGL($reference, $penjualanAcc->account_id, $penjualanAcc->account->account_name, $penjualanAcc->cd, $totalAmount, $currencyId);
    // postingGL($reference, $ppnKeluaranAcc->account_id, $ppnKeluaranAcc->account->account_name, $ppnKeluaranAcc->cd, $tax_total, $currencyId);
    postingGL($reference, $discAcc->account_id, $discAcc->account->account_name, $discAcc->cd, $disc_total, $currencyId);

    if ($returnType == 'REFUND') {
        postingGL($reference, $piutangAcc->account_id, $piutangAcc->account->account_name, $piutangAcc->cd, $net_total, $currencyId);
    }
    if ($returnType == 'DEPOSIT') {
        postingGL($reference, $depositAcc->account_id, $depositAcc->account->account_name, $depositAcc->cd, $net_total, $currencyId);
    }

    return $hdrId;
}

function getMaxReturCustomer($customer = 0)
{
    $datadb = DB::table('customer')->where('id', $customer)->first();
    // ambil sales retur dalam satu bulan

    $total_refund = DB::table('sales_return')
        ->where('customer_id', $customer)
        ->whereMonth('created_at', date('m'))
        ->whereYear('created_at', date('Y'))
        ->whereNull('deleted')
        ->where('status', '!=', 'CANCELLED')
        ->sum('refund_amount');

    if ($total_refund >= $datadb->max_retur) {
        return false;
    }

    return true;
}

function getMaxReturKaryawan($karyawan = 0)
{
    $datadb = DB::table('karyawan')->where('id', $karyawan)->first();

    $total_refund = DB::table('sales_return as sr')
        ->join('sales_invoice_header as sih', 'sih.id', 'sr.invoice_id')
        ->join('delivery_order_header as doh', 'doh.id', 'sih.do_id')
        ->join('sales_order_headers as soh', 'soh.id', 'doh.so_id')
        ->where('soh.salesman', $karyawan)
        ->whereMonth('sr.created_at', date('m'))
        ->whereYear('sr.created_at', date('Y'))
        ->whereNull('sr.deleted')
        ->where('sr.status', '!=', 'CANCELLED')
        ->sum('sr.refund_amount');

    if ($total_refund >= $datadb->max_retur) {
        return false;
    }

    return true;
}

function sanitizeDecimal($value): float
{
    // Hapus NBSP (\xC2\xA0 / \u00a0), spasi biasa, titik ribuan, dan karakter non-numerik
    // kecuali minus dan koma/titik desimal
    $clean = preg_replace('/[\x{00A0}\x{202F}\x{2009}\s]/u', '', (string) $value);
    // Jika format ribuan pakai titik (1.000.000) → hapus titik ribuan
    // Jika desimal pakai koma (1.500,50) → ganti koma jadi titik
    $clean = preg_replace('/\.(?=\d{3}(?:[.,]|$))/', '', $clean);
    $clean = str_replace(',', '.', $clean);

    return doubleval($clean);
}
