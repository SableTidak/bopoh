<?php get_header(); ?>
<div class="container">
  <div class="bopoh-layout">
    <aside class="sidebar-left">
      <?php dynamic_sidebar('sidebar-left'); ?>
    </aside>

    <main class="main-content">
      <?php while (have_posts()) : the_post(); ?>
        <article class="post-card">
          <h1><?php the_title(); ?></h1>
          <div class="post-content"><?php the_content(); ?></div>
        </article>
      <?php endwhile; ?>
    </main>

    <aside class="sidebar-right">
      <?php dynamic_sidebar('sidebar-right'); ?>
    </aside>
  </div>
</div>
<?php get_footer(); ?>