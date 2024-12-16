<?php
/**
 * Plugin Name: Task Timer
 * Description: Plugin para gestionar tareas con cronómetro por proyecto
 * Version: 1.0
 * Author: ablancodev
 * Author URI: https://ablancodev.com
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) exit;

class TaskTimerManager {
    public function __construct() {
        add_action('init', array($this, 'register_post_types'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }

    public function register_post_types() {
        // Registrar Custom Post Type para Proyectos
        register_post_type('proyecto', array(
            'public' => true,
            'labels' => array(
                'name' => 'Proyectos',
                'singular_name' => 'Proyecto'
            ),
            'supports' => array('title', 'editor'),
            'show_in_rest' => true
        ));

        // Registrar Custom Post Type para Tareas
        register_post_type('tarea', array(
            'public' => true,
            'labels' => array(
                'name' => 'Tareas',
                'singular_name' => 'Tarea'
            ),
            'supports' => array('title', 'editor'),
            'show_in_rest' => true
        ));
    }

    public function enqueue_scripts() {
        // Registrar Tailwind CSS
        wp_enqueue_style('tailwindcss', 'https://cdn.tailwindcss.com');
        
        // Registrar nuestros scripts
        wp_enqueue_script('task-timer', plugins_url('js/task-timer.js', __FILE__), array('jquery'), time(), true);
        
        // Localizar script
        wp_localize_script('task-timer', 'taskTimerObj', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('task-timer-nonce')
        ));
    }

    public function register_rest_routes() {
        register_rest_route('task-timer/v1', '/guardar-tiempo', array(
            'methods' => 'POST',
            'callback' => array($this, 'guardar_tiempo'),
            'permission_callback' => function () {
                return true; //is_user_logged_in();
            }
        ));

        register_rest_route('task-timer/v1', '/nueva-tarea', array(
            'methods' => 'POST',
            'callback' => array($this, 'nueva_tarea'),
            'permission_callback' => function () {
                return true; //is_user_logged_in();
            }
        ));

        register_rest_route('task-timer/v1', '/nuevo-proyecto', array(
            'methods' => 'POST',
            'callback' => array($this, 'nuevo_proyecto'),
            'permission_callback' => function () {
                return true;
            }
        ));

        register_rest_route('task-timer/v1', '/eliminar-proyecto', array(
            'methods' => 'POST',
            'callback' => array($this, 'eliminar_proyecto'),
            'permission_callback' => function () {
                return true;
            }
        ));

        register_rest_route('task-timer/v1', '/eliminar-tarea', array(
            'methods' => 'POST',
            'callback' => array($this, 'eliminar_tarea'),
            'permission_callback' => function () {
                return true;
            }
        ));
    }

    public function eliminar_tarea($request) {
        $params = $request->get_params();
        
        //if (!wp_verify_nonce($params['nonce'], 'task-timer-nonce')) {
        //    return new WP_Error('invalid_nonce', 'Nonce inválido', array('status' => 403));
        //}

        $tarea_id = intval($params['tarea_id']);
        
        wp_delete_post($tarea_id, true);
        
        return array(
            'success' => true,
            'mensaje' => 'Tarea eliminada correctamente'
        );
    }

    public function eliminar_proyecto($request) {
        $params = $request->get_params();
        
        //if (!wp_verify_nonce($params['nonce'], 'task-timer-nonce')) {
        //    return new WP_Error('invalid_nonce', 'Nonce inválido', array('status' => 403));
        //}

        $proyecto_id = intval($params['proyecto_id']);
        
        $tareas = get_posts(array(
            'post_type' => 'tarea',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'proyecto_id',
                    'value' => $proyecto_id
                )
            )
        ));
        
        foreach ($tareas as $tarea) {
            wp_delete_post($tarea->ID, true);
        }
        
        wp_delete_post($proyecto_id, true);
        
        return array(
            'success' => true,
            'mensaje' => 'Proyecto eliminado correctamente'
        );
    }

    public function nueva_tarea($request) {
        $params = $request->get_params();
        
        //if (!wp_verify_nonce($params['nonce'], 'task-timer-nonce')) {
        //    return new WP_Error('invalid_nonce', 'Nonce inválido', array('status' => 403));
        //}

        $proyecto_id = intval($params['proyecto_id']);
        $titulo = sanitize_text_field($params['titulo']);
        
        $tarea_id = wp_insert_post(array(
            'post_title' => $titulo,
            'post_type' => 'tarea',
            'post_status' => 'publish'
        ));
        
        update_post_meta($tarea_id, 'proyecto_id', $proyecto_id);
        
        return array(
            'success' => true,
            'mensaje' => 'Tarea creada correctamente',
            'tarea_id' => $tarea_id
        );
    }

    // nuevo proyecto
    public function nuevo_proyecto($request) {
        $params = $request->get_params();
        
        //if (!wp_verify_nonce($params['nonce'], 'task-timer-nonce')) {
        //    return new WP_Error('invalid_nonce', 'Nonce inválido', array('status' => 403));
        //}

        $titulo = sanitize_text_field($params['titulo']);
        
        $proyecto_id = wp_insert_post(array(
            'post_title' => $titulo,
            'post_type' => 'proyecto',
            'post_status' => 'publish'
        ));
        
        return array(
            'success' => true,
            'mensaje' => 'Proyecto creado correctamente',
            'proyecto_id' => $proyecto_id
        );
    }

    public function guardar_tiempo($request) {
        $params = $request->get_params();
        
       

        $tarea_id = intval($params['tarea_id']);
        $tiempo = sanitize_text_field($params['tiempo']);
        
        update_post_meta($tarea_id, 'tiempo_tarea', $tiempo);
        
        return array(
            'success' => true,
            'mensaje' => 'Tiempo guardado correctamente'
        );
    }

    public function mostrar_listado_tareas() {
        ob_start();
        include(plugin_dir_path(__FILE__) . 'templates/listado-tareas.php');
        return ob_get_clean();
    }

    public function mostrar_listado_proyectos() {
        ob_start();
        include(plugin_dir_path(__FILE__) . 'templates/listado-proyectos.php');
        return ob_get_clean();
    }
}

// Inicializar el plugin
$task_timer_manager = new TaskTimerManager();

// Registrar shortcode
add_shortcode('task_timer', array($task_timer_manager, 'mostrar_listado_tareas'));

// projects shortcode
add_shortcode('task_projects', array($task_timer_manager, 'mostrar_listado_proyectos'));

// sumer tiempos
function sumar_tiempos($tiempo1, $tiempo2) {
    $t1 = explode(':', $tiempo1);
    $t2 = explode(':', $tiempo2);
    
    $horas = intval($t1[0]) + intval($t2[0]);
    $minutos = 0;
    if ( isset($t1[1]) && isset($t2[1]) ) {
        $minutos = intval($t1[1]) + intval($t2[1]);
    }
    $segundos = 0;
    if ( isset($t1[2]) && isset($t2[2]) ) {
        $segundos = intval($t1[2]) + intval($t2[2]);
    }
    
    if ($segundos >= 60) {
        $segundos -= 60;
        $minutos++;
    }
    
    if ($minutos >= 60) {
        $minutos -= 60;
        $horas++;
    }
    
    return sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
}