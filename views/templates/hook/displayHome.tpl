<select class="mymodule-select-category">
    <!-- Desde aquí Ajax -->
    <option value="-1">{l s='Seleccionar una categoría' mod='mymodule'}</option>
    <!-- Desde aquí Ajax -->
    {foreach item=category from=$mymodule.comboCategories} 
        <option value="{$category['id_category']}">{$category['name']}</option>
    {/foreach}
</select>

{$mymodule.html['values'][$mymodule.currentLanguage] nofilter}

<div class="mymodule-result">
    {foreach item=product from=$mymodule.getProductByCategoryId}
        <div>
             <a href="{$product['link']}">
                <img src="{$product['coverProduct']['url']}" alt="{$product['coverProduct']['legend']}" />
            </a>

            <h2 itemprop="name">
                <a href="{$product['link']}">
                    {$product['name']}
                </a>
            </h2>

            <div itemprop="description">
                {$product['description'] nofilter}
            </div>

            <div>
                {$product["price"]}
            </div>
        </div>
    {/foreach}
</div>

<script>
    var combo = ".mymodule-select-category",
        resultSelector = '.mymodule-result',
        getProduct= "{$mymodule.path}views/ajax/getProductByCatId.php";
</script>