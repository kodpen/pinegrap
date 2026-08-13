<?php if ($number_of_screens > 1): ?>
	<nav class="mt-3 navigation " aria-label="data pagination"> 
		<ul class="pagination pagination-sm flex-wrap justify-content-center">
			<?php if ($previous): ?>
				<li class="page-item mt-1 mb-1"><a class="page-link" href="<?=h(escape_url($_SERVER['PHP_SELF']))?>?screen=<?=h($previous)?>" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>
			<?php else: ?>
				<li class="page-item mt-1 mb-1 disabled"><a class="page-link" href="#!" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>
			<?php endif ?>
			<?php for ($i = 1; $i <= $number_of_screens; $i++): ?>
				<li class="page-item mt-1 mb-1 <?php if ($i == $screen): ?>active<?php endif; ?>"><a class="page-link " href="<?=h(escape_url($_SERVER['PHP_SELF']))?>?screen=<?=h($i)?>"><?=h($i)?></a></li>			
			<?php endfor ?>
			<?php if ($next <= $number_of_screens): ?>
				<li class="page-item mt-1 mb-1"><a class="page-link" href="<?=h(escape_url($_SERVER['PHP_SELF']))?>?screen=<?=h($next)?>" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>
			<?php else: ?>
				<li class="page-item mt-1 mb-1 disabled"><a class="page-link" href="#!" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>
			<?php endif ?>
		</ul>
	</nav>
<?php endif ?>


