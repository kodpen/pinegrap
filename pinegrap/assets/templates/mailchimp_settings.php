<main id="content" class="container">
    <div class="row">
        <div class="col-12">
			<?=$form->get_messages()?>
            <div class="row mb-2  flex-wrap">
                <div class="col-12 col-sm-12 text-center text-md-start">
                    <h2 
						class="d-inline-block" 
						data-bs-content="<?=lang('Auto-export customers, orders, & products to MailChimp regularly. Requires cron job (job.php).')?>" 
						title="<?=lang('MailChimp Settings')?>">
						<?=lang('MailChimp Settings')?>
					</h2>
                </div>
            </div>
            <form method="post">
				<?=get_token_field()?>
                <div class="row">
                    <div class="col-12">
                        <div class="card my-4">
                            <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                                <?=lang('Dialog Options')?>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 my-2">
                    					<div class="form-check form-switch">
                    					    <input value="1" id="mailchimp" name="mailchimp" class="form-check-input collapse-switcher" type="checkbox" role="switch" data-bs-target="#mailchimp_row" />
                    					    <label class="form-check-label" for="mailchimp"><?=lang('MailChimp Sync')?></label>
                    					</div>
                    					<div class="collapse popover  fade bs-popover-bottom p-0 mb-2 w-100" id="mailchimp_row">
                    					    <div class="popover-arrow" style="position: absolute; left: 0px; transform: translate(59px, 0px);"></div>
                    					    <div class="popover-body">
                    					        <div class="row">
                    					            <div class="col-12 col-md-8 col-lg-6 my-1">
														<label for="mailchimp_key"><?=lang('API Key')?></label>
														<input
															type="text"
															id="mailchimp_key"
															name="mailchimp_key"
															class="form-control"
															maxlength="100">
													</div>
                    					            <div class="col-12 col-md-4 col-lg-3 my-1">
														<label for="mailchimp_list_id"><?=lang('List ID')?></label>
														<input
															type="text"
															id="mailchimp_list_id"
															name="mailchimp_list_id"
															class="form-control"
															maxlength="100">
													</div>
                    					            <div class="col-12 col-md-4 col-lg-3 my-1">
														<label for="mailchimp_store_id"><?=lang('Store ID')?></label>
														<input
															type="text"
															id="mailchimp_store_id"
															name="mailchimp_store_id"
															class="form-control"
															maxlength="100">
													</div>
                    					            <div class="col-12 col-md-6 col-lg-4 my-1">
														<label for="mailchimp_store_id" title="<?=lang('Set how far in the past to sync orders. Leave blank to sync all historical orders.')?>"><?=lang('Historical Sync')?> (?)</label>
														<div class="input-group">
															<input
																type="text"
																id="mailchimp_store_id"
																name="mailchimp_store_id"
																class="form-control text-end"
																inputmode="numeric" 
																data-inputmask-alias="decimal"
																maxlength="100">
															<div class="input-group-text" ><?=lang('days in the past')?></div>
														</div>
													</div>
                    					            <div class="col-12 col-md-6 col-lg-4 my-1">
														<label for="mailchimp_sync_limit" title="<?=lang('max number of orders to sync each time cron job runs')?>"><?=lang('Limit Sync')?> (?)</label>
														<div class="input-group">
															<input
																type="text"
																id="mailchimp_sync_limit"
																name="mailchimp_sync_limit"
																class="form-control text-end"
																inputmode="numeric" 
																data-inputmask-alias="decimal"
																maxlength="100">
														</div>
													</div>
                                    				<div class="col-12 my-2">
                    									<div class="form-check form-switch">
                    									    <input value="1" id="mailchimp_automation" name="mailchimp_automation" class="form-check-input" type="checkbox" role="switch"/>
                    									    <label class="form-check-label" for="mailchimp_automation" ><?=lang('Automation')?><br/><div class="form-text text-danger"><?=lang('only enable after all historical orders have been synced, to start sending MailChimp automation campaigns')?></div></label>
                    									</div>
														
                    								</div>
                    							</div>
                    						</div>
                    					</div>
                    				</div>
                    			</div>
                    		</div>
                    	</div>
                    </div>
                </div>
				<nav class="buttons navigation text-center position-sticky mb-4" style="bottom:.5rem;" aria-label="data edit buttons ">
                    <div class="container">
                        <div class=" btn-group flex-wrap justify-content-center">
                            <button type="submit" id="save_button" name="submit_button" value="Save" class="btn my-1 btn-success " data-loading-content="<?=lang(array('string'=>'Saving') )?>"><span class="material-icons me-2">save</span><span class="btn-text" ><?=lang(array('string'=>'Save') )?></span></button>
						</div>
                    </div>
                </nav>
			</form>
        </div>
    </div>
</main>