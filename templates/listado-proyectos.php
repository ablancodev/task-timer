<?php
// Fetch projects from the database, que sean mias
$projects = get_posts(array(
    'post_type' => 'proyecto',
    'posts_per_page' => -1,
    'author' => get_current_user_id()
));
?>
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6">Proyectos</h2>
        
        <!-- Formulario para nueva tarea -->
        <form id="nuevo-proyecto-form" class="mb-8">
            <div class="flex gap-4">
                <input type="text" id="titulo-proyecto" 
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Nuevo proyecto...">
                <button type="submit" 
                    class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                    Añadir Proyecto
                </button>
            </div>
        </form>

        <!-- Listado de proyectos -->
        <?php if ($projects) : ?>
            <?php foreach ($projects as $project) :                 
                // tiempo_total de las tareas del proyecto
                $tareas = get_posts(array(
                    'post_type' => 'tarea',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => 'proyecto_id',
                            'value' => $project->ID
                        )
                    )
                ));

                $tiempo_total = '00:00:00';
                foreach ($tareas as $tarea) {
                    $tiempo = get_post_meta($tarea->ID, 'tiempo_tarea', true);
                    $tiempo_total = sumar_tiempos($tiempo_total, $tiempo);
                }

                ?>
                <div class="proyecto-item bg-gray-50 p-4 rounded-lg" data-proyecto-id="<?php echo $project->ID; ?>">
                    <div class="flex items center justify-between">
                        <h3 class="text-lg font-semibold"><?php echo $project->post_title; ?></h3>
                        <p class="text-sm text-gray-500">
                            <?php echo $tiempo_total ?: '00:00:00'; ?>
                        </p>
                        <div class="flex items center gap-4">
                            <button class="ver-tareas bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Ver Tareas
                            </button>
                            <button class="eliminar-proyecto bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600" data-proyecto-id="<?php echo $project->ID; ?>">
                                Eliminar Proyecto
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No hay proyectos aún.</p>
        <?php endif; ?>
    </div>
</div>
