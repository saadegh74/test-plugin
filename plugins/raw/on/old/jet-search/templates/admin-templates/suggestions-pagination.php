<div
        class="jet-search-suggestions-pagination-wrapper"
>
    <div class="jet-search-suggestions-toolbar">
        <select v-model="bulkActionLocal" name="select" class="cx-vui-select">
            <option value=""><?php esc_html_e( 'Bulk actions', 'jet-search' ); ?></option>
            <option value="trash"><?php esc_html_e( 'Move to Trash', 'jet-search' ); ?></option>
        </select>

        <cx-vui-button
                size="mini"
                :disabled="!canApply"
                @click="onApplyBulk"
        >
            <template slot="label"><?php esc_html_e( 'Apply', 'jet-search' ); ?></template>
        </cx-vui-button>
    </div>

    <h4 class="jet-search-suggestions-prepage-count">
        {{perPageInfo()}}
    </h4>
    <cx-vui-pagination
            v-if="perPage < totalItems"
            :total="totalItems"
            :page-size="currentPerPage"
            :current="currentPageNumber"
            @on-change="changePage"
    ></cx-vui-pagination>

    <div class="jet-search-suggestions-prepage">
        <h4 class="jet-search-suggestions-prepage-text">
            <?php esc_html_e( 'Results per page', 'jet-search' ); ?>
        </h4>
        <input
            class="jet-search-suggestions-prepage-input"
            type="number"
            min="1"
            max="1000"
            v-model.number.lazy="currentPerPage"
        >
    </div>
</div>