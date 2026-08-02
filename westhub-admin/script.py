import sys

with open('C:/Users/USER/Desktop/Westhub/westhub-admin/resources/views/livewire/admin/gallery/index.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Update Item Modal
content = content.replace('''    @if()
        <div class="admin-modal-backdrop" wire:click="closeItemModal"></div>
        <section class="admin-modal-panel max-h-[90vh] overflow-y-auto" role="dialog" aria-modal="true">''',
'''    <div x-show="itemModalOpen" x-cloak>
        <div class="admin-modal-backdrop" @click="itemModalOpen = false; .closeItemModal()"></div>
        <section class="admin-modal-panel max-h-[90vh] overflow-y-auto" role="dialog" aria-modal="true">
            <div x-show="isLoadingItem" class="p-24 flex justify-center">
                <x-admin.icon name="refresh" class="h-8 w-8 animate-spin text-admin-muted" />
            </div>
            <div x-show="!isLoadingItem" class="contents">''')

content = content.replace('''            <footer class="mt-5 flex flex-wrap justify-end gap-2">
                <button type="button" wire:click="closeItemModal" class="admin-ghost-btn">Cancel</button>
                <button type="button" wire:click="saveItem" class="admin-primary-btn">{{  ? 'Save Changes' : 'Create Item' }}</button>
            </footer>
        </section>
    @endif''',
'''            <footer class="mt-5 flex flex-wrap justify-end gap-2">
                <button type="button" @click="itemModalOpen = false; .closeItemModal()" class="admin-ghost-btn">Cancel</button>
                <button type="button" wire:click="saveItem" @click="itemModalOpen = false" class="admin-primary-btn">{{  ? 'Save Changes' : 'Create Item' }}</button>
            </footer>
            </div>
        </section>
    </div>''')


# Update Delete Modal
content = content.replace('''    @if()
        <div class="admin-modal-backdrop" wire:click="cancelDelete"></div>
        <section class="admin-modal-panel max-w-xl" role="dialog" aria-modal="true">
            <p class="text-xs uppercase tracking-[0.14em] text-admin-muted">Delete Media</p>
            <h3 class="mt-2 text-2xl font-semibold">Confirm deletion</h3>
            <p class="mt-2 text-admin-muted">
                Delete <strong>{{  }}</strong>? This action cannot be undone.
            </p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" wire:click="cancelDelete" class="admin-ghost-btn">Cancel</button>
                <button type="button" wire:click="confirmDelete" class="admin-primary-btn">Delete</button>
            </div>
        </section>
    @endif''',
'''    <div x-show="deleteModalOpen" x-cloak>
        <div class="admin-modal-backdrop" @click="deleteModalOpen = false; .cancelDelete()"></div>
        <section class="admin-modal-panel max-w-xl" role="dialog" aria-modal="true">
            <p class="text-xs uppercase tracking-[0.14em] text-admin-muted">Delete Media</p>
            <h3 class="mt-2 text-2xl font-semibold">Confirm deletion</h3>
            <p class="mt-2 text-admin-muted">
                Delete <strong x-text="deleteItemTitle"></strong>? This action cannot be undone.
            </p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" @click="deleteModalOpen = false; .cancelDelete()" class="admin-ghost-btn">Cancel</button>
                <button type="button" wire:click="confirmDelete" @click="deleteModalOpen = false" class="admin-primary-btn">Delete</button>
            </div>
        </section>
    </div>''')

# Update Bulk Delete Modal
content = content.replace('''    @if()
        <div class="admin-modal-backdrop" wire:click="cancelBulkDelete"></div>
        <section class="admin-modal-panel max-w-xl" role="dialog" aria-modal="true">
            <p class="text-xs uppercase tracking-[0.14em] text-admin-muted">Bulk Delete</p>
            <h3 class="mt-2 text-2xl font-semibold">Confirm bulk delete</h3>
            <p class="mt-2 text-admin-muted">
                Delete {{ count() }} selected item{{ count() === 1 ? '' : 's' }}? This action cannot be undone.
            </p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" wire:click="cancelBulkDelete" class="admin-ghost-btn">Cancel</button>
                <button type="button" wire:click="confirmBulkDelete" class="admin-primary-btn">Delete Selected</button>
            </div>
        </section>
    @endif''',
'''    <div x-show="bulkDeleteModalOpen" x-cloak>
        <div class="admin-modal-backdrop" @click="bulkDeleteModalOpen = false; .cancelBulkDelete()"></div>
        <section class="admin-modal-panel max-w-xl" role="dialog" aria-modal="true">
            <p class="text-xs uppercase tracking-[0.14em] text-admin-muted">Bulk Delete</p>
            <h3 class="mt-2 text-2xl font-semibold">Confirm bulk delete</h3>
            <p class="mt-2 text-admin-muted">
                Delete {{ count() }} selected item{{ count() === 1 ? '' : 's' }}? This action cannot be undone.
            </p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" @click="bulkDeleteModalOpen = false; .cancelBulkDelete()" class="admin-ghost-btn">Cancel</button>
                <button type="button" wire:click="confirmBulkDelete" @click="bulkDeleteModalOpen = false" class="admin-primary-btn">Delete Selected</button>
            </div>
        </section>
    </div>''')


# Update Layout Blocks
content = content.replace('''    @elseif( === 'list')
        <div class="glass-card p-4 space-y-2">''',
'''    @else
        <div x-show="uiViewMode === 'list'" x-cloak>
            <div class="glass-card p-4 space-y-2">''')

content = content.replace('''            @endforeach
        </div>
    @elseif( === 'masonry')
        <div class="columns-1 gap-4 sm:columns-2 xl:columns-4">''',
'''            @endforeach
            </div>
        </div>
        <div x-show="uiViewMode === 'masonry'" x-cloak>
            <div class="columns-1 gap-4 sm:columns-2 xl:columns-4">''')

content = content.replace('''            @endforeach
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">''',
'''            @endforeach
            </div>
        </div>
        <div x-show="uiViewMode === 'grid'" x-cloak>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">''')

content = content.replace('''            @endforeach
        </div>
    @endif

    <div class="glass-card p-3">{{ ->links() }}</div>''',
'''            @endforeach
            </div>
        </div>
    @endif

    <div class="glass-card p-3">{{ ->links() }}</div>''')

with open('C:/Users/USER/Desktop/Westhub/westhub-admin/resources/views/livewire/admin/gallery/index.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
