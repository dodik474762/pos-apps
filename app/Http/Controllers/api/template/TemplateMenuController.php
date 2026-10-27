<?php

namespace App\Http\Controllers\api\template;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class TemplateMenuController extends Controller
{
    public function generateMenuWeb()
    {
        $tree = $this->getGroupedMenuTree();

        return $this->renderMenuWeb($tree);
    }

    public function getAccurateMenuTree()
    {
        return $this->getGroupedMenuTree();
    }

    protected function getGroupedMenuTree()
    {
        $rows = $this->getPermittedMenus();

        return $this->buildGroupedTree($rows);
    }

    protected function getPermittedMenus()
    {
        $user_id = Session::get('user_id');

        return DB::table('menu as m')
            ->distinct()
            ->select([
                'm.id',
                'm.nama',
                'm.icon',
                'm.menu_alias',
                'm.url',
                'm.parent',
                'm.sort',
                'up.insert',
                'up.update',
                'up.delete',
                'up.view',
                'up.print',
            ])
            ->join('users_permissions as up', 'up.menu', 'm.id')
            ->join('users_group as ug', 'ug.id', 'up.users_group')
            ->join('users as u', 'u.user_group', 'ug.id')
            ->where('u.id', $user_id)
            ->whereNull('up.deleted')
            ->orderBy('m.parent')
            ->orderBy('m.sort')
            ->get();
    }

    protected function buildGroupedTree($rows)
    {
        $byUrl = [];
        foreach ($rows as $row) {
            $url = trim((string) $row->url);
            if ($url === '' || $url === '-') {
                continue;
            }
            if (! isset($byUrl[$url])) {
                $byUrl[$url] = $row;
            }
        }

        $used = [];
        $tree = [];

        foreach ($rows as $row) {
            $parent = (string) $row->parent;
            $url = trim((string) $row->url);
            if (($parent === '' || $parent === '0') && $url !== '' && $url !== '-') {
                $node = (array) $row;
                $node['children'] = [];
                $tree[] = $node;
                $used[$url] = true;
            }
        }

        foreach ($this->getMenuGroups() as $group) {
            $node = $this->buildGroupNode($group, $byUrl, $used);
            if ($node !== null) {
                $tree[] = $node;
            }
        }

        $others = [];
        foreach ($byUrl as $url => $row) {
            if (isset($used[$url])) {
                continue;
            }
            $node = (array) $row;
            $node['children'] = [];
            $others[] = $node;
        }
        if (! empty($others)) {
            $tree[] = [
                'id' => 'grp-others',
                'nama' => 'Lainnya',
                'icon' => 'ri-more-2-line',
                'url' => '-',
                'menu_alias' => null,
                'children' => $others,
            ];
        }

        return $tree;
    }

    protected function buildGroupNode($group, $byUrl, &$used)
    {
        $children = [];
        foreach ($group['children'] as $child) {
            if (isset($child['menu'])) {
                $url = $child['menu'];
                if (isset($byUrl[$url]) && ! isset($used[$url])) {
                    $node = (array) $byUrl[$url];
                    $node['children'] = [];
                    $children[] = $node;
                    $used[$url] = true;
                }
            } else {
                $sub = $this->buildGroupNode($child, $byUrl, $used);
                if ($sub !== null) {
                    $children[] = $sub;
                }
            }
        }

        if (empty($children)) {
            return null;
        }

        return [
            'id' => 'grp-'.$group['key'],
            'nama' => $group['label'],
            'icon' => $group['icon'],
            'url' => '-',
            'menu_alias' => null,
            'children' => $children,
        ];
    }

    protected function isNodeActive($node)
    {
        $url = trim((string) ($node['url'] ?? ''));
        if ($url !== '' && $url !== '-') {
            if (
                request()->is($url) ||
                request()->is($url.'/index') ||
                request()->is($url.'/add') ||
                request()->is($url.'/ubah')
            ) {
                return true;
            }
        }
        foreach (($node['children'] ?? []) as $child) {
            if ($this->isNodeActive($child)) {
                return true;
            }
        }

        return false;
    }

    protected function renderMenuWeb($nodes, $level = 0)
    {
        $html = '';
        foreach ($nodes as $node) {
            $node = (array) $node;
            $children = $node['children'] ?? [];
            $hasChildren = ! empty($children);
            $url = trim((string) ($node['url'] ?? ''));
            $isLink = ! $hasChildren && $url !== '' && $url !== '-';
            $label = ($level > 0 && ! empty($node['menu_alias'])) ? $node['menu_alias'] : $node['nama'];
            $icon = ($level === 0 && ! empty($node['icon'])) ? "<i class='".$node['icon']."'></i>" : '';
            $active = $this->isNodeActive($node) ? 'active' : '';

            if ($isLink) {
                $html .= "<li class='nav-item'><a href='".URL::to($url)."' class='nav-link ".$active."'>".$icon.'<span>'.$label.'</span></a></li>';
            } elseif ($hasChildren) {
                $expanded = $this->isNodeActive($node) ? 'true' : 'false';
                $show = $this->isNodeActive($node) ? 'show' : '';
                $html .= "<li class='nav-item'><a href='#collapse-left-menu-".$node['id']."' class='nav-link ".$active."' data-bs-toggle='collapse' role='button' aria-expanded='".$expanded."' aria-controls='collapse-left-menu-".$node['id']."'>".$icon.'<span>'.$label.'</span></a>';
                $html .= "<div class='collapse menu-dropdown ".$show."' id='collapse-left-menu-".$node['id']."'><ul class='nav nav-sm flex-column'>".$this->renderMenuWeb($children, $level + 1).'</ul></div></li>';
            } else {
                $html .= "<li class='nav-item'><a href='#' class='nav-link'>".$icon.'<span>'.$label.'</span></a></li>';
            }
        }

        return $html;
    }

    protected function getMenuGroups()
    {
        return [
            [
                'key' => 'master',
                'label' => 'Master Data',
                'icon' => 'ri-database-2-line',
                'children' => [
                    ['menu' => 'master/company'],
                    ['menu' => 'master/company_profile'],
                    ['menu' => 'master/customer_category'],
                    ['menu' => 'master/customer'],
                    ['menu' => 'master/customer_limit_top'],
                    ['menu' => 'master/vendor'],
                    ['menu' => 'master/product_type'],
                    ['menu' => 'master/unit'],
                    ['menu' => 'master/product'],
                    ['menu' => 'master/product_catalog'],
                    ['menu' => 'master/promo_item'],
                    ['menu' => 'master/warehouse'],
                    ['menu' => 'master/pasar'],
                    ['menu' => 'master/branch'],
                    ['menu' => 'master/tax'],
                    ['menu' => 'master/karyawan'],
                    ['menu' => 'master/group'],
                    ['menu' => 'master/job_category'],
                    ['menu' => 'master/travel_item'],
                    ['menu' => 'master/travel_cost'],
                    ['menu' => 'master/dayoff'],
                    ['menu' => 'master/working_hours'],
                    [
                        'key' => 'wilayah',
                        'label' => 'Wilayah',
                        'icon' => 'ri-map-pin-line',
                        'children' => [
                            ['menu' => 'master/province'],
                            ['menu' => 'master/city'],
                            ['menu' => 'master/kecamatan'],
                            ['menu' => 'master/kelurahan'],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'purchasing',
                'label' => 'Purchasing',
                'icon' => 'ri-shopping-cart-2-line',
                'children' => [
                    ['menu' => 'transaksi/purchase_order'],
                    ['menu' => 'transaksi/good-receipt'],
                    ['menu' => 'transaksi/purchase-invoice'],
                    ['menu' => 'transaksi/vendor-bill'],
                    ['menu' => 'transaksi/purchase-return'],
                ],
            ],
            [
                'key' => 'sales',
                'label' => 'Sales',
                'icon' => 'ri-shopping-bag-3-line',
                'children' => [
                    ['menu' => 'transaksi/sales_plan'],
                    ['menu' => 'transaksi/sales_order'],
                    ['menu' => 'transaksi/delivery_order'],
                    ['menu' => 'transaksi/invoice'],
                    ['menu' => 'transaksi/sales_invoice'],
                    ['menu' => 'transaksi/sales_payment'],
                    ['menu' => 'transaksi/sales_return'],
                    ['menu' => 'transaksi/credit_note'],
                    ['menu' => 'transaksi/terima_uang'],
                    ['menu' => 'transaksi/return_cs'],
                    ['menu' => 'transaksi/packing_list'],
                    ['menu' => 'transaksi/packing_list_pr'],
                    ['menu' => 'transaksi/pl_tagihan'],
                    ['menu' => 'transaksi/sessions'],
                    ['menu' => 'transaksi/visit_plan'],
                    ['menu' => 'transaksi/forecast'],
                    ['menu' => 'transaksi/spk_jasa_instalasi'],
                ],
            ],
            [
                'key' => 'inventory',
                'label' => 'Inventory',
                'icon' => 'ri-archive-2-line',
                'children' => [
                    ['menu' => 'transaksi/adjustment_stock'],
                    ['menu' => 'transaksi/closing-stock'],
                    ['menu' => 'report/stock-card'],
                    ['menu' => 'report/inventory-valuation'],
                ],
            ],
            [
                'key' => 'hr',
                'label' => 'Human Resource',
                'icon' => 'ri-user-3-line',
                'children' => [
                    ['menu' => 'transaksi/presensi'],
                    ['menu' => 'transaksi/employee_leave'],
                    ['menu' => 'transaksi/perdin'],
                    ['menu' => 'transaksi/realisasi_perdin'],
                    ['menu' => 'transaksi/payment_perdin'],
                    ['menu' => 'transaksi/perdin_after_sales'],
                    ['menu' => 'transaksi/pengembalian'],
                    ['menu' => 'transaksi/reimbursment'],
                    ['menu' => 'transaksi/budgeting'],
                ],
            ],
            [
                'key' => 'accounting',
                'label' => 'Accounting',
                'icon' => 'ri-money-dollar-circle-line',
                'children' => [
                    [
                        'key' => 'accounting-master',
                        'label' => 'Master',
                        'icon' => 'ri-book-2-line',
                        'children' => [
                            ['menu' => 'master/coa'],
                            ['menu' => 'master/accounts'],
                            ['menu' => 'master/account_types'],
                            ['menu' => 'master/account_mapping_rules'],
                            ['menu' => 'master/accounting_periods'],
                        ],
                    ],
                    [
                        'key' => 'accounting-transactions',
                        'label' => 'Transactions',
                        'icon' => 'ri-exchange-dollar-line',
                        'children' => [
                            ['menu' => 'transaksi/journals'],
                            ['menu' => 'monitoring/closing-period'],
                        ],
                    ],
                    [
                        'key' => 'accounting-reports',
                        'label' => 'Reports',
                        'icon' => 'ri-file-chart-line',
                        'children' => [
                            ['menu' => 'report/general-ledger'],
                            ['menu' => 'report/trial-balance'],
                            ['menu' => 'transaksi/ar_subledger'],
                            ['menu' => 'transaksi/ap_subledger'],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'reports',
                'label' => 'Reports',
                'icon' => 'ri-bar-chart-box-line',
                'children' => [
                    ['menu' => 'report/report_penjualan'],
                    ['menu' => 'report/sales_detail'],
                    ['menu' => 'report/sales_shipment'],
                    ['menu' => 'report/gross_profit'],
                    ['menu' => 'report/sales-return'],
                    ['menu' => 'report/report_pembayaran'],
                    ['menu' => 'report/report_piutang'],
                    ['menu' => 'report/customer'],
                    ['menu' => 'report/delivery'],
                    ['menu' => 'report/item-received'],
                    ['menu' => 'report/report_stock'],
                    ['menu' => 'report/report_visit'],
                    ['menu' => 'report/summary_visit'],
                    ['menu' => 'report/perdin-item'],
                    ['menu' => 'report/forecast_detail'],
                    ['menu' => 'report/forecast_vs_sales'],
                    ['menu' => 'report/budgeting_report'],
                ],
            ],
            [
                'key' => 'approval',
                'label' => 'Approval',
                'icon' => 'ri-check-double-line',
                'children' => [
                    ['menu' => 'approval/purchase_order'],
                    ['menu' => 'approval/invoice'],
                    ['menu' => 'approval/customer'],
                    ['menu' => 'approval/customer_limit_top'],
                    ['menu' => 'approval/bugeting'],
                    ['menu' => 'approval/perdin'],
                    ['menu' => 'approval/realisasi_perdin'],
                    ['menu' => 'approval/perdin_after_sales'],
                    ['menu' => 'approval/approval_reimbursement'],
                    ['menu' => 'approval/approval_leave'],
                    ['menu' => 'approval/spk'],
                ],
            ],
            [
                'key' => 'billing',
                'label' => 'Langganan',
                'icon' => 'ri-bank-card-line',
                'children' => [
                    ['menu' => 'billing/subscription'],
                    ['menu' => 'billing/invoices'],
                ],
            ],
            [
                'key' => 'superadmin-billing',
                'label' => 'Super Admin Billing',
                'icon' => 'ri-shield-star-line',
                'children' => [
                    ['menu' => 'admin/billing/subscriptions'],
                    ['menu' => 'admin/billing/plans'],
                    ['menu' => 'admin/billing/invoices'],
                    ['menu' => 'admin/billing/webhooks'],
                ],
            ],
            [
                'key' => 'settings',
                'label' => 'Settings',
                'icon' => 'ri-settings-3-line',
                'children' => [
                    ['menu' => 'master/users'],
                    ['menu' => 'master/roles'],
                    ['menu' => 'master/permission'],
                    ['menu' => 'master/menu'],
                    ['menu' => 'master/routing'],
                    ['menu' => 'transaksi/generate_project'],
                ],
            ],
        ];
    }
}
