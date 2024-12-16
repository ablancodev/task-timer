<!-- templates/listado-tareas.php -->
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6">Tareas del Proyecto</h2>
        
        <!-- Formulario para nueva tarea -->
        <form id="nueva-tarea-form" class="mb-8">
            <div class="flex gap-4">
                <input type="text" id="titulo-tarea" 
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Nueva tarea...">
                <!-- select de proyectos -->
                 <?php
                 $projects = get_posts(array(
                     'post_type' => 'proyecto',
                     'posts_per_page' => -1
                 ));
                ?>
                <select name="proyecto-id" id="proyecto-id" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <?php foreach ($projects as $project) : ?>
                        <option value="<?php echo $project->ID; ?>">
                            <?php echo $project->post_title; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" 
                    class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                    Añadir Tarea
                </button>
            </div>
        </form>

        <!-- Listado de tareas -->
        <div id="listado-tareas" class="space-y-4">
            <?php
            $tareas = get_posts(array(
                'post_type' => 'tarea',
                'posts_per_page' => -1
            ));

            foreach ($tareas as $tarea) :
                $tiempo = get_post_meta($tarea->ID, 'tiempo_tarea', true);
            ?>
                <div class="tarea-item bg-gray-50 p-4 rounded-lg" data-tarea-id="<?php echo $tarea->ID; ?>">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold"><?php echo $tarea->post_title; ?></h3>
                        <p class="text-sm text-gray-500">
                            <?php // project title
                            $proyecto_id = get_post_meta($tarea->ID, 'proyecto_id', true);
                            echo get_the_title($proyecto_id);
                            ?>
                        </p> 
                        <div class="flex items-center gap-4">
                            <div class="tiempo-container">
                                <span class="tiempo-display font-mono">
                                    <?php echo $tiempo ?: '00:00:00'; ?>
                                </span>
                            </div>
                            <div class="flex gap-2">
                                <button class="start-timer bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                    Iniciar
                                </button>
                                <button class="stop-timer bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 hidden">
                                    Detener
                                </button>
                                <button class="manual-time bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                                    Editar Tiempo
                                </button>
                                <button class="eliminar-tarea bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600" data-tarea-id="<?php echo $tarea->ID; ?>">
                                    <!-- icono de borrar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
