<main id="content" class="container">
    <div class="row ">
        <div class="col-12">
            <div class="row mb-2  flex-wrap">
                <div class="col-12 col-sm-12 col-md-6 col-xl-9 text-center text-md-start">
                    <h2 class="d-inline-block text-break" data-bs-content="<?php echo lang('Copy & paste the code below into your custom layout.')?>" title="<?php echo lang('Generate Layout')?>">[<?=h($page['name'])?>]</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 my-2">
                                    <?=$form->field(array(
                                        'type' => 'textarea',
                                        'name' => 'layout',
                                        'id' => 'layout',
                                        'class' => 'd-none h-100'))?>
                                    <?=get_codemirror_includes()?>
                                    <?=get_codemirror_javascript(array(
                                        'id' => 'layout',
                                        'code_type' => 'php',
                                        'readonly' => true))?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
