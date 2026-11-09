<?php $title = 'Devoluciones'; require_once __DIR__ . '/header.php';
$rows = q("SELECT p.id,h.nombre,CONCAT(w.nombre,' ',w.apellido) tr,p.cantidad-p.cantidad_devuelta pend,p.fecha_prevista FROM prestamos p JOIN herramientas h ON h.id=p.herramienta_id JOIN personal w ON w.id=p.personal_id WHERE p.estado<>'devuelto' ORDER BY p.fecha_prevista")->fetchAll();
?>
<div class="wrap">
    <table>
        <tr>
            <th>Herramienta</th>
            <th>Trabajador</th>
            <th>Pendiente</th>
            <th>Prevista</th>
            <th>Registrar devolución</th>
        </tr>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= e($r['nombre']) ?></td>
            <td><?= e($r['tr']) ?></td>
            <td><?= e($r['pend']) ?></td>
            <td>
                <?= e($r['fecha_prevista']) ?> 
                <?= $r['fecha_prevista'] < date('Y-m-d') ? '<span class="tag pendiente">atrasado</span>' : '' ?>
            </td>
            <td>
                <form class="in" method="post" action="registrar_devolucion.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="prestamo_id" value="<?= $r['id'] ?>">
                    
                    <div style="display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap;">
                        <label style="font-size: 11px; display: flex; flex-direction: column; font-weight: 500;">
                            Devueltas:
                            <input type="number" name="cantidad" min="1" max="<?= $r['pend'] ?>" value="<?= $r['pend'] ?>" style="width:70px">
                        </label>
                        
                        <label style="font-size: 11px; display: flex; flex-direction: column; font-weight: 500;">
                            Dañadas:
                            <input type="number" name="danada" min="0" value="0" style="width:70px">
                        </label>
                        
                        <label style="font-size: 11px; display: flex; flex-direction: column; font-weight: 500; flex-grow: 1;">
                            Observaciones:
                            <input name="observaciones" placeholder="Ej: Roto, falla..." style="width:100%">
                        </label>
                        
                        <button class="btn">Devolver</button>
                    </div>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>