<div>
	<cx-vui-list-table
		:is-empty="! itemsList.length"
		empty-message="<?php _e( 'No relations found', 'jet-engine' ); ?>"
	>
		<cx-vui-list-table-heading
			:slots="[ 'name', 'post_types', 'type', 'hash', 'actions' ]"
			class-name="cols-5"
			slot="heading"
		>
			<span slot="name"><?php _e( 'Name', 'jet-engine' ); ?></span>
			<span slot="post_types"><?php _e( 'Related post types', 'jet-engine' ); ?></span>
			<span slot="type"><?php _e( 'Relation type', 'jet-engine' ); ?></span>
			<span slot="hash"><?php _e( 'Relation meta key', 'jet-engine' ); ?></span>
			<span slot="actions"><?php _e( 'Actions', 'jet-engine' ); ?></span>
		</cx-vui-list-table-heading>
		<cx-vui-list-table-item
			:slots="[ 'name', 'post_types', 'type', 'hash', 'actions' ]"
			class-name="cols-5"
			slot="items"
			v-for="item in itemsList"
			:key="item.id"
		>
			<span slot="name">
				<a
					:href="getEditLink( item.id )"
					class="jet-engine-title-link"
				>{{ item.name }}</a>
			</span>
			<i slot="post_types">{{ item.post_type_1 }} -> {{ item.post_type_2 }}</i>
			<i slot="type">{{ relationsTypes[ item.type ] }}</i>
			<code slot="hash">{{ item.hash }}</code>
			<div slot="actions">
				<a :href="getEditLink( item.id )"><?php _e( 'Edit', 'jet-engine' ); ?></a>
				|
				<a
					class="jet-engine-delete-item"
					href="#"
					@click.prevent="deleteItem( item )"
				><?php _e( 'Delete', 'jet-engine' ); ?></a>
			</div>
		</cx-vui-list-table-item>
	</cx-vui-list-table>
	<jet-cpt-delete-dialog
		v-if="showDeleteDialog"
		v-model="showDeleteDialog"
		:item-id="deletedItem.id"
	></jet-cpt-delete-dialog>
</div>