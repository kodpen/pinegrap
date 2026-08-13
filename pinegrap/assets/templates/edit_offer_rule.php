<main id="content" class="container">
    <div class="row">
        <div class="col-12">
			<?=$form->get_messages()?>
            <div class="row mb-2  flex-wrap">
                <div class="col-12 col-sm-12 text-center text-md-start">
                    <nav class="navigation" aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center justify-content-md-start "> 
                            <li class="breadcrumb-item"><a class="link-secondary " data-loading-content="<?=lang('Loading')?>" href="<?=OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY?>/view_offer_rules.php"><?=lang('All Offer Rules')?></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php if ($screen == 'create'): ?><?=lang('Create Offer Rule')?><?php else: ?><?=lang('Edit Offer Rule')?><?php endif ?></li>
                        </ol> 
                    </nav>
                    <h2 
						class="d-inline-block text-break header-content-for-add-page" 
						data-bs-content="<?php if ($screen == 'create'): ?><?=lang('Create a new offer rule that can be assigned to any offer.')?><?php else: ?><?=lang('Edit an offer rule that can be assigned to any offer.')?><?php endif ?>" 
						title="<?php if ($screen == 'create'): ?><?=lang('Create Offer Rule')?><?php else: ?><?=lang('Edit Offer Rule')?><?php endif ?>">
						[<?php if ($screen == 'create'): ?><?=lang('Offer Rule Name')?><?php else: ?><?=h($offer_rule['name'])?><?php endif ?>]
					</h2>
                </div>
            </div>
            <form method="post">
				<?=get_token_field()?>
                <div class="row">
                    <div class="col-12">
                        <div class="card my-4">
                            <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                                <?=lang('Rule Options')?>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-sm-6 my-2">
                                        <label for="name" class="form-label"><?=lang('Offer Rule Name')?></label>
                                        <input type="text" name="name" id="name" class="form-control add-header-content-updater" maxlength="50" />
										<div class="invalid-feedback"><?=lang('Required Area')?></div>
                                    </div>
									<div class="col-12  my-2">
										<div class="row">
											<div class="col-12 ">
												<div class="alert alert-primary">
													<h4 class="alert-heading"><?=lang('Require a Subtotal')?></h4>
													<p><?=lang('Require that the customer\'s cart contain at least a certain subtotal. You may leave the field blank, if the rule does not require a subtotal.')?></p>
												</div>
											</div>
                                    		<div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-2">
                                    		    <label for="required_subtotal" class="form-label"><?=lang('Required Subtotal')?></label>
                                    		    <div class="input-group">
                                    		        <input value="0" type="text" name="required_subtotal" id="required_subtotal" class="form-control" maxlength="12" inputmode="numeric" data-inputmask-alias="currency" data-inputmask-groupSeparator="," data-inputmask-digits="2" data-inputmask-digitsOptional="false" data-inputmask-placeholder="0"  style="text-align: right;" />
                                    		        <label class="input-group-text" for="price"><?=BASE_CURRENCY_SYMBOL?></label>
                                    		    </div>
                                    		</div>
										</div>
									</div>
									<div class="col-12 my-2">
										<div class="row">
											<div class="col-12 ">
												<div class="alert alert-primary">
													<h4 class="alert-heading"><?=lang('Require a Product')?></h4>
													<p><?=lang('Select a product that the customer must add to the cart, in order to get the offer. If the customer should have the option of adding one of many products, then you may select multiple products. In that case, the customer will only be required to add one of the products (not all). You may leave the field blank if the rule does not require a product. You should also enter the quantity of the product(s) that the customer must add to the cart.')?></p>
												</div>
											</div>
                                    		<div class="col-12">
                                    		    <label for="required_products" class="form-label"><?=lang('Required Product')?></label>
                                    		   	<select style="width:100%" class="select2 form-select" data-placeholder="<?=lang('Click to select product(s)')?>" id="required_products" name="required_products[]" multiple="multiple"></select>
												<script>$(function() {$('#required_products').on('select2:select', function() {if ($('#required_quantity').val() == '') {$('#required_quantity').val('1');}});});</script>
                                    		</div>
                                            <div class="col-12 col-md-6 col-lg-4 col-xl-3 my-3">
                                                <label for="required_quantity" class="form-label "><?=lang('Required Quantity')?></label>
                                                <div class="input-group number-controls">
                                                    <button class="btn material-icons minus border border-end-0" type="button">remove</button>
                                                    <input class="form-control text-center border-start-0 border-end-0" value="" type="text" name="required_quantity" id="required_quantity" maxlength="9" inputmode="numeric" data-inputmask-alias="decimal"  data-inputmask-placeholder="0"/>
                                                    <button class="btn material-icons plus border border-start-0" type="button">add</button>
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
                            <button type="submit" id="save_button" name="submit_save" value="<?php if ($screen == 'create'): ?>Create<?php else: ?>Save<?php endif ?>" class="btn my-1 btn-success " data-loading-content="<?=lang(array('string'=>'Saving') )?>"><span class="material-icons me-2"><?php if ($screen == 'create'): ?>add<?php else: ?>save<?php endif ?></span><span class="btn-text" ><?php if ($screen == 'create'): ?><?=lang(array('string'=>'Create') )?><?php else: ?><?=lang(array('string'=>'Save') )?><?php endif ?></span></button>
                            <?php if ($screen == 'edit'): ?>
								<button type="submit" name="delete" value="Delete" class="btn my-1  btn-danger " data-loading-content="<?=lang(array('string'=>'Deleting') )?>" data-confirm-content="<?=lang(array('string'=>'WARNING: This {var:1} will be permanently deleted.','vars'=>array(lang('offer rule'))))?>"><span class="material-icons me-2">delete</span><span class="btn-text" ><?=lang(array('string'=>'Delete') )?></span></button>
							<?php endif ?>
						</div>
                    </div>
                </nav>
			</form>
        </div>
    </div>
</main>