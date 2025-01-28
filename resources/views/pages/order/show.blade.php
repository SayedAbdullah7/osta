<div class="">
    <table class="table text-capitalize">
        <thead>
        <tr class="fw-bold fs-6 text-gray-800">
            <th>Name</th>
            <th>Value</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>id</td>
            <td>{{$model->id}}</td>
        </tr>
        <tr>
            <td>status</td>
            <td>{!! \App\DataTables\Custom\OrderDataTable::formatStatusBadge($model) !!}</td>
        </tr>
        <tr>
            <td>category</td>
            <td>{{$model->category}}</td>
        </tr>
        <tr>
            <td>description</td>
            <td>{{$model->desc}}</td>
        </tr>
        <tr>
            <td>invoice</td>
            <td><a class="" href="{{route('invoice.order.index', $model->id)}}">{{$model->invoice?->id}}</a></td>
        </tr>
        <tr>
            <td>service</td>
            <td>{{$model->service?->name}}</td>
        </tr>
        <tr>
            <td>user</td>
            <td><a class="has_action" href="#" data-action="{{route('user.edit', $model->user_id??0)}}">{{$model->user?->fullName}}</a></td>
        </tr>
        <tr>
            <td>provider</td>
            <td><a class="has_action" href="#" data-action="{{route('provider.edit', $model->provider_id??0)}}">{{$model->provider?->fullName}}</a></td>
        </tr>

        </tbody>
    </table>

    <h3>Sub services</h3>
    <table class="table table-rounded table-striped border gy-7 gs-7">
        <thead>
        <tr class="fw-semibold fs-6 text-gray-800 border-bottom border-gray-200">
            <th>Id</th>
            <th>name</th>
            <th>max price</th>
            <th>type</th>
            <th>quantity</th>
            <th>space name</th>
        </tr>
        </thead>
        <tbody>
        @foreach($model->orderSubServices as $raw)
        <tr>
            <td>{{$raw->subService?->id}}</td>
            <td>{{$raw->subService?->name}}</td>
            <td>{{$raw->max_price}}</td>
            <td>{{$raw->type}}</td>
            <td>{{$raw->quantity}}</td>
            <td>{{$raw->space_name}}</td>
        </tr>
        @endforeach

        </tbody>
    </table>
    @if(!empty($images = $model->getMedia('images')??[]))
    <h3>Images</h3>
    <div class="row">
        <x-media-gallery :media-items="$model->getMedia('images')??[]" />
    </div>
    @endif
    @if(!empty($voiceUrl = $model->getFirstMediaUrl('voice_desc')))
        <a href="{{$voiceUrl}}">
            Voice description
        </a>
    @endif

{{--    <h3>Invoices</h3>--}}
{{--    <table class="table table-rounded table-striped border gy-7 gs-7">--}}
{{--        <thead>--}}
{{--        <tr class="fw-semibold fs-6 text-gray-800 border-bottom border-gray-200">--}}
{{--            <th>Id</th>--}}
{{--            <th>uuisd</th>--}}
{{--            <th>max price</th>--}}
{{--            <th>type</th>--}}
{{--            <th>quantity</th>--}}
{{--            <th>space name</th>--}}
{{--        </tr>--}}
{{--        </thead>--}}
{{--        <tbody>--}}
{{--        @foreach($model->orderSubServices as $raw)--}}
{{--            <tr>--}}
{{--                <td>{{$raw->subService?->id}}</td>--}}
{{--                <td>{{$raw->subService?->name}}</td>--}}
{{--                <td>{{$raw->max_price}}</td>--}}
{{--                <td>{{$raw->type}}</td>--}}
{{--                <td>{{$raw->quantity}}</td>--}}
{{--                <td>{{$raw->space_name}}</td>--}}
{{--            </tr>--}}
{{--        @endforeach--}}

{{--        </tbody>--}}
{{--    </table>--}}
</div>

