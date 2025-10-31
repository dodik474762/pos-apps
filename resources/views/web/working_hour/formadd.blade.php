<input type="hidden" id="id" value="{{ isset($id) ? $id : '' }}">

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
                <form onsubmit="WorkingHour.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label>Choose Day</label>
                                <div>
                                    <select name="day" id="day" class="form-control required" error="Day">
                                        <option value="">Day</option>
                                        @foreach ($list_day as $item)
                                            <option value="{{ $item }}"
                                                {{ isset($data->day) ? ($item == $data->day ? 'selected' : '') : '' }}>
                                                {{ $item }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Start Hour</label>
                                <div>
                                    <input type="time" id="start_hour" class="form-control required" error="Start Hour"
                                        placeholder="Start Hour" value="{{ isset($data->start_hour) ? $data->start_hour : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>End Hour</label>
                                <div>
                                    <input type="time" id="end_hour" class="form-control required" error="End Hour"
                                        placeholder="End Hour" value="{{ isset($data->end_hour) ? $data->end_hour : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Holiday</label>
                                <div>
                                   <input type="checkbox" name="holiday" id="holiday" {{ isset($data->holiday) ? ($data->holiday == 1 ? 'checked' : '') : '' }}>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Remarks</label>
                                <div>
                                    <input type="text" id="remarks" class="form-control required" error="Remarks"
                                        placeholder="Remarks" value="{{ isset($data->remarks) ? $data->remarks : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-end" style="margin-bottom: 10px;">
            <div>
                <button type="submit" onclick="WorkingHour.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="WorkingHour.cancel(this, event)" class="btn waves-effect">
                    Cancel
                </button>
            </div>
        </div>
        <!-- end select2 -->

    </div>


</div>
<!-- end row -->
