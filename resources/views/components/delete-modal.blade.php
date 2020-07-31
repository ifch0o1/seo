{{-- Bulk delete modal --}}
<div class="modal modal-danger fade" tabindex="-1" id="custom_delete_modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <i class="voyager-trash"></i> {{ __('voyager::generic.are_you_sure_delete') }} 
                    <span>@{{ delete_name }}</span>?
                </h4>
            </div>
            <div class="modal-body">
                @{{ delete_text }}

                <p v-if="getDeleteCount" class="text-danger font-bold"> @{{ getDeleteCount }} items for delete </p>
            </div>
            <div class="modal-footer">
                {{-- {{ csrf_field() }} --}}

                {{-- DELETE BUTTON --}}
                <span class="btn btn-danger pull-right"  @click="confirmDelete($event)">
                    <span v-if="!deleting">{{ __('voyager::generic.bulk_delete_confirm') }}</span>
                    <span v-else>Deleting...</span>
                </span>

                {{-- CANCEL BUTTON --}}
                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">
                    {{ __('voyager::generic.cancel') }}
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->