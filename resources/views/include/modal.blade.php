<!-- Button trigger modal -->
{{--            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm">--}}
{{--                Launch demo modal--}}
{{--            </button>--}}

<!-- Modal -->

<!-- Modal -->
<div class="modal fade" id="modalForm" aria-labelledby="modalFormLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalFormLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add an SVG loader -->
                <div id="loader" style="display: none;height: 370px;">
                    <img alt="" src="{{ asset('images/gif/loading.gif') }}" height="370" width="100%">
                </div>
                <!-- End SVG loader -->
                <div id="content"></div>
            </div>
{{--            <div class="modal-footer">--}}
{{--                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>--}}
{{--                <button type="button" class="btn btn-primary" id="submit">Save changes</button>--}}
{{--            </div>--}}
        </div>
    </div>
</div>
