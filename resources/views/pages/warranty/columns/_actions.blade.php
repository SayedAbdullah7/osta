<a href="#" class="has_action btn btn-icon btn-light-warning me-5 " data-type="edit"
   data-action="{{route('warranty.edit', [$model->id])}}">
    <i class="fa-solid fa-pen-to-square"></i>
</a>
<a href="#" class="delete_btn btn btn-icon btn-light-danger me-5  delete_btn" data-type="delete" data-action="{{route('warranty.destroy', [$model->id])}}" >
    <i class="fa-solid fa-trash"></i>
</a>
