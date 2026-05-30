<?php

namespace App\Http\Controllers\api\master;

use App\Http\Controllers\Controller;
use App\Models\Master\PromoItem;
use App\Models\Master\PromoItemProduct;
use App\Models\Master\PromoItemProductFree;
use App\Models\Master\PromoItemProductSyarat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromoItemController extends Controller
{
    public function getTableName()
    {
        return "product_promo_item";
    }

    public function getData()
    {
        DB::enableQueryLog();
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
            ])
            ->whereNull('m.deleted')
            ->orderBy('m.id', 'desc');
        if (isset($_POST)) {
            $data['recordsTotal'] = $datadb->get()->count();
            if (isset($_POST['search']['value'])) {
                $keyword = $_POST['search']['value'];
                $datadb->where(function ($query) use ($keyword) {
                    $query->where('m.promo_name', 'LIKE', '%' . $keyword . '%');
                });
            }
            if (isset($_POST['order'][0]['column'])) {
                $datadb->orderBy('m.id', $_POST['order'][0]['dir']);
            }
            $data['recordsFiltered'] = $datadb->get()->count();

            if (isset($_POST['length'])) {
                $datadb->limit($_POST['length']);
            }
            if (isset($_POST['start'])) {
                $datadb->offset($_POST['start']);
            }
        }
        $data['data'] = $datadb->get()->toArray();
        $data['draw'] = $_POST['draw'];
        $query = DB::getQueryLog();
        // echo '<pre>';
        // print_r($query);die;
        return json_encode($data);
    }

    public function submit(Request $request)
    {
        $data = $request->all();
        // echo '<pre>';
        // print_r($data);
        // die;
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...

            list($product_uom, $product_id, $product_name, $unit_name) = explode('//', $data['promo_item'][0]['product']);
            $units = DB::table('unit')->where('name', $unit_name)->first();
            $unitsId = $units->id;

            $roles = $data['id'] == '' ? new PromoItem() : PromoItem::find($data['id']);
            $roles->promo_name = $data['promo_name'];
            $roles->min_qty = $data['min_qty'];
            $roles->max_qty = $data['max_qty'];
            $roles->discount_type = $data['disc_type'];
            $roles->discount_value = $data['disc_value'];
            $roles->kategori = $data['kategori'];
            $roles->date_start = $data['date_start'];
            $roles->min_mix = $data['min_mix'];
            $roles->max_mix = $data['max_mix'];
            $roles->kelipatan = $data['kelipatan'];
            $roles->potong_grand_total = $data['potong_grand_total'];
            $roles->channel_outlet = $data['channel_outlet'];
            $roles->sub_channel_outlet = $data['sub_channel_outlet'];
            $roles->additional_disc_type = $data['additional_disc_type'];
            $roles->additional_disc = $data['additional_disc'];
            $roles->beban = $data['beban'];
            $roles->kategori_disc = $data['kategori_disc'];
            $roles->unit = $unitsId;
            $roles->save();
            $headerId = $roles->id;

            PromoItemProduct::where('product_promo_item', $headerId)->delete();
            foreach ($data['promo_item'] as $key => $value) {
                if ($value['remove'] != '1') {
                    list($product_uom, $product_id, $product_name, $unit_name) = explode('//', $value['product']);
                    $units = DB::table('unit')->where('name', $unit_name)->first();
                    $unitsId = $units->id;

                    $items = new PromoItemProduct();
                    $items->product_promo_item = $headerId;
                    $items->product = $product_id;
                    $items->unit = $unitsId;
                    $items->product_uom = $product_uom;
                    $items->save();
                } else {
                    if ($value['id'] != '') {
                        PromoItemProduct::where('id', $value['id'])->delete();
                    }
                }
            }

            PromoItemProductFree::where('product_promo_item', $headerId)->delete();
            if (isset($data['free_product'])) {
                foreach ($data['free_product'] as $key => $value) {
                    if ($value['remove'] != '1') {
                        if ($value['product'] != '') {
                            list($product_uom, $product_id, $product_name, $unit_name) = explode('//', $value['product']);
                            $units = DB::table('unit')->where('name', $unit_name)->first();
                            $unitsId = $units->id;

                            $items = new PromoItemProductFree();
                            $items->product_promo_item = $headerId;
                            $items->free_product = $product_id;
                            $items->free_unit = $unitsId;
                            $items->free_qty = $value['qty'];
                            $items->product_uom = $product_uom;
                            $items->product_name = $product_name;
                            $items->unit_name = $unit_name;
                            $items->save();
                        }
                    } else {
                        if ($value['id'] != '') {
                            PromoItemProductFree::where('id', $value['id'])->delete();
                        }
                    }
                }
            }

            PromoItemProductSyarat::where('product_promo_item', $headerId)->delete();
            if (isset($data['product_syarat'])) {
                foreach ($data['product_syarat'] as $key => $value) {
                    if ($value['remove'] != '1') {
                        if ($value['product'] != '') {
                            list($product_uom, $product_id, $product_name, $unit_name) = explode('//', $value['product']);
                            $units = DB::table('unit')->where('name', $unit_name)->first();
                            $unitsId = $units->id;

                            $items = new PromoItemProductSyarat();
                            $items->product_promo_item = $headerId;
                            $items->product = $product_id;
                            $items->unit = $unitsId;
                            $items->qty = $value['qty'] ? $value['qty'] : 0;
                            $items->nominal = $value['nominal'] ? $value['nominal'] : 0;
                            $items->product_uom = $product_uom;
                            $items->save();
                        }
                    } else {
                        if ($value['id'] != '') {
                            PromoItemProductSyarat::where('id', $value['id'])->delete();
                        }
                    }
                }
            }

            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            //throw $th;
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }
        return response()->json($result);
    }

    public function confirmDelete(Request $request)
    {
        $data = $request->all();
        $result['is_valid'] = false;
        DB::beginTransaction();
        try {
            //code...
            $menu = PromoItem::find($data['id']);
            $menu->deleted = date('Y-m-d H:i:s');
            $menu->save();

            DB::commit();
            $result['is_valid'] = true;
        } catch (\Throwable $th) {
            //throw $th;
            $result['message'] = $th->getMessage();
            DB::rollBack();
        }
        return response()->json($result);
    }

    public function getDetailData($id)
    {
        DB::enableQueryLog();
        $datadb = DB::table($this->getTableName() . ' as m')
            ->select([
                'm.*',
            ])->where('m.id', $id);
        $data = $datadb->first();
        $query = DB::getQueryLog();
        return response()->json($data);
    }

    public function delete(Request $request)
    {
        $data = $request->all();
        return view('web.promo_item.modal.confirmdelete', $data);
    }

    public function showDataUsers(Request $request)
    {
        $data = $request->all();
        return view('web.promo_item.modal.datausers', $data);
    }
}
