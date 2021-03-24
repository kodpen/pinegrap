
<?=$messages?>

<form <?=$attributes?>>

    <div class="row mb24">
    <div class="col-sm-12">
    <h4>
        Shipping Address
        <?php if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient'): ?>
            for <strong><?=h($ship_to_name)?></strong>
        <?php endif ?>
    </h4>
    </div>
    </div>

    <div class="row mb24">
    <div class="col-sm-3">
    <div class="form-group">
        <label for="salutation">Salutation</label>
        <div class="select-option">
    		<i class="ti-angle-down"></i>
        	<select name="salutation" id="salutation"></select>
		</div>
    </div>
    </div>
    <div class="col-sm-3">
    <div class="form-group">
        <label for="first_name">First Name*</label>
        <input type="text" name="first_name" id="first_name" class="form-control">
    </div>    
    </div>

    <div class="col-sm-3">
    <div class="form-group">
        <label for="last_name">Last Name*</label>
        <input type="text" name="last_name" id="last_name" class="form-control">
    </div>
    </div>
        
    <div class="col-sm-3">
    <div class="form-group">
        <label for="phone_number">Phone</label> (delivery issues)
        <input type="tel" name="phone_number" id="phone_number" class="form-control">
    </div>
    </div>        
        
    </div>  
        
    <div class="row">
    <div class="col-sm-4">
    <div class="form-group">
        <label for="company">Company</label>
        <input type="text" name="company" id="company" class="form-control">
    </div>
    </div>
        
    <div class="col-sm-4">    
    <div class="form-group">
        <label for="address_1">Address 1*</label>
        <input type="text" name="address_1" id="address_1" class="form-control">
    </div>
    </div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="address_2">Address 2</label>
        <input type="text" name="address_2" id="address_2" class="form-control">
    </div>
	</div>
    </div>
    
    <div class="row">
    <div class="col-sm-4">
    <div class="form-group">
        <label for="city">City*</label>
        <input type="text" name="city" id="city" class="form-control">
    </div>
    </div>

    <div class="col-sm-4">
    <div class="form-group">
        <label for="state_text_box">State / Province</label>
        <input type="text" name="state" id="state_text_box" class="form-control">
        
        <label for="state_pick_list" style="display: none">State / Province*</label>
        <div class="select-option">
    		<i class="ti-angle-down"></i>
        	<select name="state" id="state_pick_list" style="display: none"></select>
        </div>
    </div>
    </div>

    <div class="col-sm-4">
    <div class="form-group">
        <label for="zip_code">Zip / Postal Code*</label>
        <input type="text" name="zip_code" id="zip_code" class="form-control">
    </div>
    </div>
    </div>
    
    <div class="row">
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="country">Country*</label>
        <div class="select-option">
    		<i class="ti-angle-down"></i>
        	<select name="country" id="country"></select>
        </div>
    </div>
    </div>
         
    <div class="col-sm-8">

    <?php if ($address_type): ?>

       <div>
            <label>Address Type*
            <?php if ($address_type_url): ?>
                &nbsp;<a href="<?=h($address_type_url)?>" target="_blank">What is this?</a>
            <?php endif ?>
           </label>
        </div>
   
        <div>
            <div class="radio-option">
                <div class="inner"></div>
                    <input type="radio" name="address_type" value="residential">
                    <span>Residential</span>
        	</div>
        </div>
        
        <div>
        	<div class="radio-option">
            	<div class="inner"></div>
                	<input type="radio" name="address_type" value="business">
                	<span>Business</span>
        	</div>
    	</div>

    <?php endif ?>

    	</div>
    </div>
        
    <?php if ($shipping_same_as_billing): ?>
    	<div class="row">
        <div class="col-sm-12">
        <div class="checkbox">
            <label class="check-box">
                <input type="checkbox" name="shipping_same_as_billing" value="1">
                <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
                <span>My billing address is the same as this shipping address.</span>
            </label>
        </div>
        </div>
        </div>
    <?php endif ?>
        
    <?php if ($custom_shipping_form): ?>
        
        <!-- Adds edit button and grid around form when user is in edit mode -->
        <?=$edit_start?>
        
        <?php if ($custom_shipping_form_title): ?>
    		<strong><?=h($custom_shipping_form_title)?></strong>
        <?php endif ?>

        <!-- instructions -->
        <div class="form-group">
            <label for="field_246"><?=$field['246']['label']?></label>
            <textarea name="field_246" id="field_246" class="form-control" placeholder="If there are any special delivery instructions, please let us know."></textarea>
        </div>

        <!-- Closes the edit grid -->
        <?=$edit_end?>

    <?php endif ?>
       

    <?php if ($arrival_dates): ?>

    	<div class="row mt48">
        <div class="col-sm-12">
        <h4>
            Requested Arrival Date
            <?php if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient'): ?>
                for <strong><?=h($ship_to_name)?></strong>
            <?php endif ?>
        </h4>
        </div>
        </div>
        
        <?php foreach($arrival_dates as $arrival_date): ?>

    		<?php if (!$arrival_date['custom']): ?>
            <div class="row pt16 pb16">
            <div class="col-sm-3">
            	<div class="radio-option">
                	<div class="inner"></div>
                	<input type="radio" name="arrival_date" id="arrival_date_<?=$arrival_date['id']?>" value="<?=$arrival_date['id']?>">
                	<span><?=h($arrival_date['name'])?></span>
            	</div>
             </div>
             <div class="col-sm-9">
                <?=$arrival_date['description']?>
            </div>
    		</div>
            <?php endif ?>
                
            <?php if ($arrival_date['custom']): ?>
            <div class="row pt0 pb24">
            <div class="col-sm-3">
                <div class="radio-option">
                	<div class="inner"></div>
                	<input type="radio" name="arrival_date" id="arrival_date_<?=$arrival_date['id']?>" value="<?=$arrival_date['id']?>">
                	<span><?=h($arrival_date['name'])?></span>
            	</div>
                <div class="form-group" style="margin-bottom:0">
                    <input type="text" name="custom_arrival_date_<?=$arrival_date['id']?>" id="custom_arrival_date_<?=$arrival_date['id']?>" class="form-control" style="margin-bottom:0" placeholder="Select Date">
                </div>
            </div>
            <div class="col-sm-9">
                <?=$arrival_date['description']?>
            </div>
            </div>
                
            <?php endif ?>

    <hr>
        <?php endforeach ?>
    
    <?php endif ?>
        
    <div class="row pt24" style="padding-left:15px">
    	<button type="submit" class="btn btn-primary btn-lg"><?=h($submit_button_label)?></button>
    </div>
    
    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>
    
</form>
