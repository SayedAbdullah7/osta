<div class="d-flex flex-row text-center" >
<a href="#" class="has_action btn btn-icon btn-light-warning me-5 " data-type="edit"
   data-action="{{route('ticket.edit', [$model->id])}}">
    <i class="fa-solid fa-pen-to-square"></i>
</a>
<a href="#" class=" openChat btn btn-icon btn-light-info me-5 " data-ticket-id="{{$model->id}}"
   data-action="{{route('user.edit', [$model->id])}}">
    <i class="fa-solid fa-headset"></i>
</a>
</div>
{{--<a href="#" class="delete_btn btn btn-icon btn-light-danger me-5  delete_btn" data-type="delete" data-action="{{route('user.destroy', [$model->id])}}" >--}}
{{--    <i class="fa-solid fa-trash"></i>--}}
{{--</a>--}}
