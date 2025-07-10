
// ... (código existente)

// Definir constantes para meta fields e taxonomias
define('RGD_META_STATUS', 'status_boleto');
define('RGD_META_VENCIMENTO', 'data_vencimento');
define('RGD_TAX_CIDADE', 'cidade');

// Enfileirar scripts
function rgd_enqueue_scripts() {
    wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com', array(), null);
    wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs', array(), null, true);
}
add_action('wp_enqueue_scripts', 'rgd_enqueue_scripts');

// AJAX: adicionar cliente (com verificação nonce)
add_action('wp_ajax_add_cliente', function () {
    check_ajax_referer('rgd_add_cliente', 'nonce');
    
    // ... (resto do código existente)
});

// Shortcode
add_shortcode('formulario_iptu', function () {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'])) {
        if (!wp_verify_nonce($_POST['rgd_nonce'], 'rgd_submit_form')) {
            wp_die('Erro de segurança');
        }
        
        // Validação do lado do servidor
        $required_fields = ['titulo', 'cliente', 'status', 'vencimento', 'cidade'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_die('Todos os campos obrigatórios devem ser preenchidos.');
            }
        }
        
        // ... (resto do código de processamento do formulário)
        
        // Implementar upload do arquivo
        if (!empty($_FILES['arquivo']['name'])) {
            $upload = wp_handle_upload($_FILES['arquivo'], array('test_form' => false));
            if (!isset($upload['error'])) {
                update_post_meta($post_id, 'gp_upload_do_arquivo', $upload['url']);
            }
        }
    }
    
    // ... (resto do código do shortcode)
    
    ob_start();
    ?>
    <form id="form-iptu" method="post" enctype="multipart/form-data" autocomplete="on">
        <?php wp_nonce_field('rgd_submit_form', 'rgd_nonce'); ?>
        <!-- ... (resto do HTML do formulário) -->
    </form>
    <?php
    return ob_get_clean();
});

// ... (resto do código existente)

function atualizar_status_boletos_vencidos() {
    $args = array(
        'post_type'      => 'iptu',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => RGD_META_STATUS,
                'value'   => 'pendente',
                'compare' => '=',
            ),
        ),
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $vencimento = get_post_meta(get_the_ID(), RGD_META_VENCIMENTO, true);
            if ($vencimento && strtotime($vencimento) < strtotime('today')) {
                update_post_meta(get_the_ID(), RGD_META_STATUS, 'vencido');
            }
        }
        wp_reset_postdata();
    }
}

// ... (resto do código existente)
