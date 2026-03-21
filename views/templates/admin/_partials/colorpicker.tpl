<div class="colorAttributeProperties">
	<div class="form-group">
		<label class="control-label col-lg-3">
			<span {if isset($colorpicker_title)}class="label-tooltip{/if} " data-toggle="tooltip" data-html="true" title="{$colorpicker_title|default}" data-original-title="{$colorpicker_original_title|default}"> {$colorpicker_label}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="form-group">
				<div class="col-lg-2">
					<div class="row">
						<div class="input-group">
							<input type="text" data-hex="true" class="color mColorPickerInput mColorPicker" name="{$colorpicker_name}" value="{$colorpicker_value}" id="{$colorpicker_name}" style="background-color: {$colorpicker_value}; color: black;"><span style="cursor:pointer;" id="icp_{$colorpicker_name}" class="mColorPickerTrigger input-group-addon" data-mcolorpicker="true"><img src="/ps1730/img/admin/color.png" style="border:0;margin:0 0 0 3px" align="absmiddle"></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>