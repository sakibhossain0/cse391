<?php
/* ============================================================
   footer.php: the bottom of every page. A page can set
   $scripts (a list of JavaScript files) before including it.
   ============================================================ */
defined('APP_RUNNING') or exit('No direct access.');
?>
</main>

<footer class="footer">
  <div class="wrap footer-inner">
    <p>Car workshop appointment system &middot; CSE391 Assignment 3 by Md. Sakib Hossain, BRAC University</p>
    <p>
      <a href="<?= $base ?>help.php">Help</a> &middot;
      <a href="https://sakibhossain0.github.io/cse391/">My CSE391 homepage</a>
    </p>
  </div>
</footer>

<?php foreach (isset($scripts) ? $scripts : [] as $script): ?>
<script src="<?= $base . $script ?>"></script>
<?php endforeach; ?>
</body>
</html>
