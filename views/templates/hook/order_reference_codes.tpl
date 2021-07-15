<table style="width: 100%">
<tr>
	<td style="width: 50%; text-align: left;">
		{if $url_code_qr}
			<img src="{$url_code_qr}" style="width:70px; height:70px;  padding:0px;" />
		{/if}
	</td>
	<td style="width: 50%; text-align: right;">
        {if $url_code_barcode}
		    <img src="{$url_code_barcode}" style="width:130px; height:65px;  padding:0px;" />
        {/if}
	</td>
</tr>
</table>