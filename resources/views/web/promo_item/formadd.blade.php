<input type="hidden" id="id" value="{{ isset($id) ? $id : '' }}">
<button type="button" id="btn-show-modal" class="" style="display: none;" data-bs-toggle="modal"
    data-bs-target="#data-modal-karyawan"></button>
<div id="content-modal-form"></div>

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Create {{ $title }}</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">{{ $title_parent }}</a></li>
                    <li class="breadcrumb-item active">Create {{ $title }}</li>
                </ol>
            </div>

        </div>
    </div>
</div>
<!-- end page title -->

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form onsubmit="RoutingApproval.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Module</label>
                                <select class="form-control select2 required" error="Module" id="menu">
                                    <option value="">Daftar Menu</option>
                                    @foreach ($list_module as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ isset($data->menu) ? ($data->menu == $item['id'] ? 'selected' : '') : '' }}>
                                            {{ $item['nama'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Remarks</label>
                                <div>
                                    <input type="text" id="remarks" class="form-control required" error="Remarks"
                                        placeholder="Remarks" value="{{ isset($data->remarks) ? $data->remarks : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Group</label>
                                <select class="form-control select2" id="group">
                                    <option value=""></option>
                                    @foreach ($groups as $item)
                                        <option value="{{ $item['term_id'] }}"
                                            {{ isset($data->group) ? ($data->group == $item['term_id'] ? 'selected' : '') : '' }}>
                                            {{ $item['keterangan'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 table-sm table-nowrap" id="table-routing">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Routing Level</th>
                                            <th>Users Approval</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($data->id))
                                            @foreach ($routing_item as $item)
                                                <tr class="input" data_id="{{ $item['id'] }}">
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <select name="routing" id="routing" class="form-control">
                                                            @foreach ($list_approval as $items)
                                                                <option value="{{ $items['term_id'] }}"
                                                                    {{ $items['term_id'] == $item['state'] ? 'selected' : '' }}>
                                                                    {{ $items['keterangan'] }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <button class="btn btn-outline-primary" type="button"
                                                                id="button-addon1"
                                                                onclick="RoutingApproval.showDataUsers(this)">Pilih</button>
                                                            <input readonly id="users" type="text"
                                                                class="form-control required" error="Users"
                                                                placeholder="Pilih Data Users"
                                                                aria-label="Pilih Data Users"
                                                                aria-describedby="button-addon1"
                                                                value="{{ $item['users'] . '//' . $item['name_user'] }}">
                                                        </div>
                                                    </td>
                                                    <td class="text-center" id="action">
                                                        <button type="button"
                                                            onclick="RoutingApproval.deleteItem(this, event)"
                                                            class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i
                                                                class="bx bx-trash-alt"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        @if (!isset($data->id))
                                            <tr class="input" data_id="">
                                                <td>&nbsp;</td>
                                                <td>
                                                    <select name="routing" id="routing" class="form-control">
                                                        @foreach ($list_approval as $item)
                                                            <option value="{{ $item['term_id'] }}">
                                                                {{ $item['keterangan'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <button class="btn btn-outline-primary" type="button"
                                                            id="button-addon1"
                                                            onclick="RoutingApproval.showDataUsers(this)">Pilih</button>
                                                        <input readonly id="users" type="text"
                                                            class="form-control required" error="Users"
                                                            placeholder="Pilih Data Users" aria-label="Pilih Data Users"
                                                            aria-describedby="button-addon1" value="">
                                                    </div>
                                                </td>
                                                <td class="text-center" id="action">
                                                    &nbsp;
                                                </td>
                                            </tr>
                                        @endif
                                        <tr class="" data_id="">
                                            <td colspan="3">
                                                <a href="javascript:;" class="btn btn-primary btn-sm"
                                                    onclick="RoutingApproval.addItem(this, event)">Add
                                                    Routing</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Users Only Reminder (Full Approved)</h5>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 table-sm table-nowrap"
                                    id="table-routing-reminder">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Users</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($data->id))
                                            @foreach ($routing_reminder_item as $item)
                                                <tr class="input" data_id="{{ $item['id'] }}">
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <div class="input-group">
                                                            <button class="btn btn-outline-primary" type="button"
                                                                id="button-addon1"
                                                                onclick="RoutingApproval.showDataUsers(this)">Pilih</button>
                                                            <input readonly id="users" type="text"
                                                                class="form-control" error="Users"
                                                                placeholder="Pilih Data Users"
                                                                aria-label="Pilih Data Users"
                                                                aria-describedby="button-addon1"
                                                                value="{{ $item['users'] . '//' . $item['name_user'] }}">
                                                        </div>
                                                    </td>
                                                    <td class="text-center" id="action">
                                                        <button type="button"
                                                            onclick="RoutingApproval.deleteItem(this, event)"
                                                            class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i
                                                                class="bx bx-trash-alt"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach

                                            @if (empty($routing_reminder_item))
                                                <tr class="input" data_id="">
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <div class="input-group">
                                                            <button class="btn btn-outline-primary" type="button"
                                                                id="button-addon1"
                                                                onclick="RoutingApproval.showDataUsers(this)">Pilih</button>
                                                            <input readonly id="users" type="text"
                                                                class="form-control" error="Users"
                                                                placeholder="Pilih Data Users"
                                                                aria-label="Pilih Data Users"
                                                                aria-describedby="button-addon1" value="">
                                                        </div>
                                                    </td>
                                                    <td class="text-center" id="action">
                                                        &nbsp;
                                                    </td>
                                                </tr>
                                            @endif
                                        @endif
                                        @if (!isset($data->id))
                                            <tr class="input" data_id="">
                                                <td>&nbsp;</td>
                                                <td>
                                                    <div class="input-group">
                                                        <button class="btn btn-outline-primary" type="button"
                                                            id="button-addon1"
                                                            onclick="RoutingApproval.showDataUsers(this)">Pilih</button>
                                                        <input readonly id="users" type="text"
                                                            class="form-control" error="Users"
                                                            placeholder="Pilih Data Users"
                                                            aria-label="Pilih Data Users"
                                                            aria-describedby="button-addon1" value="">
                                                    </div>
                                                </td>
                                                <td class="text-center" id="action">
                                                    &nbsp;
                                                </td>
                                            </tr>
                                        @endif
                                        <tr class="" data_id="">
                                            <td colspan="3">
                                                <a href="javascript:;" class="btn btn-primary btn-sm"
                                                    onclick="RoutingApproval.addReminderItem(this, event)">Add
                                                    Item</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-end">
            <div>
                <button type="submit" onclick="RoutingApproval.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="RoutingApproval.cancel(this, event)" class="btn waves-effect">
                    Cancel
                </button>
            </div>
        </div>
        <!-- end select2 -->

    </div>


</div>
<!-- end row -->
