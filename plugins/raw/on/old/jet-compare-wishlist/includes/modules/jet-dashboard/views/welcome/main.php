<div
	class="jet-dashboard-welcome-page"
	:class="{ 'proccesing-state': proccesingState }"
>
	 
	<div class="jet-dashboard-welcome-page__content">
		<div
			class="jet-dashboard-page__panel jet-dashboard-welcome-page__registered-plugins"
		>
			<div class="cx-vui-subtitle">
				<span class="cx-vui-subtitle__label"><?php _e( 'افزونه های نصب شده', 'jet-dashboard' ); ?><br><br>
				 برای خرید افزونه های دیگر الان وارد سایت شوید <a href="https://elementor-site.ir"> لینک خرید </a> </span>
			</div>
			<div class="plugin-list plugin-list--registered-plugins">
				<plugin-item-registered
					v-for="( pluginData, index ) in registeredPluginList"
					:key="index"
					:plugin-data="pluginData"
				></plugin-item-registered>
			</div>
		</div>
		 
	</div>
</div>
