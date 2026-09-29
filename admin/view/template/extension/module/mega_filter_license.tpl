<?php require DIR_TEMPLATE . 'extension/module/' . $_name . '-header.tpl'; ?>

<br /><br />
<center>
	<font style="font-weight:300; font-size: 30px;">Thank you for choosing</font> <font style="font-weight:400; font-size: 30px;">Mega Filter PRO</font>
</center>
<br />
<hr />

<table style="margin: 0 auto;">
	<tbody>
		<?php if ( ! empty( $_error_warning2 ) ) { ?>
		<tr><td><div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $_error_warning2; ?>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				</div></td></tr>
		<?php } ?>
		<?php if( ! empty( $files ) ) { ?>
			<tr>
				<td class="text-center">
					Make sure that these files exist and that has permissions to write to them:<br /><br />
					<pre class="text-left"><?php echo implode( '<br />', $files ); ?></pre>
					<br />
					
					<a href="<?php echo $action; ?>" class="btn btn-primary"><i class="glyphicon glyphicon-refresh"></i> Check permissions again</a>
					<br />
					<br />
					<br />
					
					<font style="font-weight:300; font-size: 20px;">How to change the file permissions ?</font>
					<br />
					<br />

					<img src="<?php echo $HTTP_URL; ?>view/stylesheet/mf/images/permissions.jpg" style="max-width: 100%; border: 1px solid #ccc; padding: 5px;" />
					
				</td>
			</tr>
		<?php } else { ?>
			<tr>
				<td class="text-center">
					To complete installation, please enter your <strong>Activation Key</strong>:
					<table style="margin: 20px auto;">
						<tr>
							<td class="text-left" style="padding: 5px; text-align: center;">
								<input type="text" name="activation_key" value="<?php echo empty( $_POST['activation_key'] ) ? '' : $_POST['activation_key']; ?>" class="form-control" style="width: 429px; font-family: 'Courier New', Courier, monospace" placeholder="_____-______-_____-_____-_____-_____-_____-_____" />
								<small>You can't find it? <a href="https://support.ocdemo.eu/auth/forgot-activation-key" target="_blank">Click here</a></small>
							</td>
						</tr>
					</table>
					<button class="btn btn-primary btn-sm" type="submit"><i class="glyphicon glyphicon-ok"></i>&nbsp;&nbsp;Confirm</button>
				</td>
			</tr>
			<tr>
				<td>
					<br />
					<br />
					<font style="font-weight:300; font-size: 20px;">Remember!</font>
					<div class="help-block">
						<ol>
							<li>If two times you enter incorrect data, the ability to activate the extension will be blocked for 48 hours.</li>
							<li>If in the meantime you changed your e-mail in the store where you purchased this extension, to activate it, you should still to use the same which was used to purchase.</li>
						</ol>
					</div>
				</td>
			</tr>
		<?php } ?>
		<tr>
			<td class="text-center">
				<br />
				<br />
				<br />
				<font style="font-weight:300; font-size: 20px;">Problems with the installation?</font>
				<br />
				<br />
				
				If you don't know how to finish installation of MFP please contact us: <a href="mailto:support@ocdemo.eu">support@ocdemo.eu</a><br />
				(don't forget to include your Activation Key and FTP details)
			</td>
		</tr>
	</tbody>
</table>
<br /><br />
<?php require DIR_TEMPLATE . 'extension/module/' . $_name . '-footer.tpl'; ?>