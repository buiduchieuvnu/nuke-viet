<!-- BEGIN: main -->
<!-- BEGIN: viewcatloop -->
<div class="news_column">
    <div class="items clearfix">
        <!-- BEGIN: image --><a href="{CONTENT.link}" title="{CONTENT.title}"><img alt="{HOMEIMGALT1}" src="{HOMEIMG1}" width="{IMGWIDTH1}" /></a><!-- END: image --><h2><a href="{CONTENT.link}" title="{CONTENT.title}">{CONTENT.title}</a></h2>
        <p>
            {CONTENT.hometext}
        </p>
        <!-- BEGIN: adminlink -->
        <p style="text-align : right;">
            {ADMINLINK}
        </p>
        <!-- END: adminlink -->
    </div>
</div>
<!-- END: viewcatloop -->
<!-- BEGIN: related -->
<hr/>
<h4>{ORTHERNEWS}</h4>
<ul class="related">
    <!-- BEGIN: loop -->
    <li>
        <a href="{RELATED.link}" title="{RELATED.title}">{RELATED.title} <span class="date">({RELATED.publtime}) </span></a>
    </li>
    <!-- END: loop -->
</ul>
<!-- END: related -->
<!-- END: main -->