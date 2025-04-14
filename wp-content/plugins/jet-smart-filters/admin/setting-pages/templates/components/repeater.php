<div class="jsf_repeater">
	<div class="jsf_repeater-label"
		 v-if="label"
	>
		{{label}}
	</div>
	<div class="jsf_repeater-items">
		<div class="jsf_repeater-itemt"
			 v-for="(item, index) in value"
		>
			<div class="jsf_repeater-itemt-content">
				<slot :item="item"
					:index="index" />
			</div>
			<div class="jsf_repeater-itemt-remove"
				 @click="onRemoveItemClick(index)"
			>
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect width="16" height="16" transform="matrix(1 0 0 -1 0 16)" fill="white"></rect>
					<path d="M2.28564 14.192V3.42847H13.7142V14.192C13.7142 14.6685 13.5208 15.0889 13.1339 15.4533C12.747 15.8177 12.3005 15.9999 11.7946 15.9999H4.20529C3.69934 15.9999 3.25291 15.8177 2.866 15.4533C2.4791 15.0889 2.28564 14.6685 2.28564 14.192Z"></path><path d="M14.8571 1.14286V2.28571H1.14282V1.14286H4.57139L5.56085 0H10.4391L11.4285 1.14286H14.8571Z"></path>
				</svg>
			</div>
		</div>
	</div>
	<div class="jsf_repeater-actions"
		 v-if="actionsEnabled"
	>
		<button class="jsf_repeater-add-buttom"
				@click="onAddItemClick"
		>
			<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M11.8332 6.83317H6.83317V11.8332H5.1665V6.83317H0.166504V5.1665H5.1665V0.166504H6.83317V5.1665H11.8332V6.83317Z"/>
			</svg>
			{{addText}}
		</button>
	</div>
</div>