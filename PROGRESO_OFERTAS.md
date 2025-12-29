# Progreso: Implementación del Sistema de Ofertas

**Fecha:** 2025-11-06
**Sesión:** Desarrollo del módulo de Ofertas

---

## Resumen

Se implementó completamente el módulo de gestión de ofertas, permitiendo crear ofertas basadas en presupuestos aprobados con selección flexible de renglones.

---

## Cambios Realizados

### 1. Rutas (routes/web.php)

#### Cambios en líneas 175-199:
- **Creación de grupo de rutas independiente** para ofertas en `/ofertas`
- Antes estaban bajo `/presupuestos/ofertas`
- Agregado rol `operador` a los permisos
- **Nueva ruta API** (línea 177):
  ```php
  Route::get('api/{presupuesto}/detalles', [PresupuestoController::class, 'obtenerDetalles'])
      ->name('api.detalles');
  ```

### 2. Backend: PresupuestoController.php

#### Nuevo método API (líneas 988-1022):
```php
public function obtenerDetalles(Presupuesto $presupuesto)
```

**Propósito:** Devolver datos del presupuesto en formato JSON para consumo desde frontend

**Carga relaciones:**
- `detalles.insumo.clasificacionEconomica`
- `notasPedido.oficina`
- `tipoCompra`

**Retorna JSON con:**
- ID y número de presupuesto
- Ejercicio y estado
- Fecha y monto total
- Array de detalles con información completa de cada renglón:
  - Renglón, insumo (código y descripción)
  - Cantidad y precio unitario
  - Especificaciones técnicas
  - Observaciones
  - Subtotal

**Fix importante:** Eliminada la carga de relación `proveedor` que no existe en el modelo Presupuesto (las ofertas tienen proveedores, no los presupuestos).

### 3. Backend: OfertaController.php

#### Cambios en rutas de renderizado:
- **Línea 86:** `'Presupuestos/Ofertas'` → `'Ofertas/Index'`
- **Línea 117:** `'Presupuestos/Ofertas/Create'` → `'Ofertas/Create'`
- **Línea 235:** `'Presupuestos/Ofertas/Show'` → `'Ofertas/Show'`
- **Línea 260:** `'Presupuestos/Ofertas/Edit'` → `'Ofertas/Edit'`

#### Cambios en redirecciones:
- **Línea 213:** `route('presupuestos.ofertas')` → `route('ofertas.index')`
- **Línea 337:** `route('presupuestos.ofertas.show', ...)` → `route('ofertas.show', ...)`
- **Línea 359:** `route('presupuestos.ofertas')` → `route('ofertas.index')`

### 4. Frontend: resources/js/Pages/Ofertas/Create.vue

#### Estructura completa implementada (709 líneas):

**Sección 1: Información General (líneas 30-80)**
- Selector de presupuesto (con carga dinámica de renglones)
- Selector de proveedor
- Fecha de oferta

**Sección 2: Condiciones Comerciales (líneas 83-126)**
- Plazo de entrega (días)
- Forma de pago
- Validez de oferta (días)

**Sección 3: Tabla de Renglones (líneas 129-283)**

Columnas:
1. **Checkbox:** Seleccionar/deseleccionar renglón para ofertar
2. **Renglón:** Número de renglón
3. **Insumo:**
   - Código del insumo
   - Nombre/descripción
   - Link "Ver descripción completa →" (abre modal)
4. **Cantidad Solicitada:** Del presupuesto (solo lectura)
5. **Precio Presupuesto:** Del presupuesto (solo lectura)
6. **Cantidad Ofertada:** Input editable (solo si checkbox activo)
7. **Precio Ofertado:** Input editable (solo si checkbox activo)
8. **Descripción:** Botón 📝 para abrir modal con detalles de la oferta
9. **Subtotal:** Calculado automáticamente

**Features de la tabla:**
- Checkbox "Seleccionar todos" en el header
- Inputs deshabilitados si el renglón no está marcado
- Cálculo automático de subtotales
- Total general al pie
- Contador: "Renglones ofertados: X de Y"

**Modal 1: Descripción del Renglón del Presupuesto (líneas 323-370)**
- Muestra información ampliada del presupuesto
- Solo lectura
- Campos:
  - Insumo (nombre y código)
  - Cantidad solicitada
  - Precio presupuestado
  - Especificaciones técnicas
  - Observaciones del presupuesto

**Modal 2: Descripción de lo Ofertado (líneas 373-463)**
- Permite agregar detalles de la oferta
- Campos editables:
  - Marca
  - Modelo
  - Características técnicas (textarea)
  - Garantía
  - Tiempo de entrega (días)
  - Observaciones del renglón (textarea)

#### Lógica JavaScript (líneas 467-709):

**Reactive data:**
```javascript
const renglones = ref([])  // Array de renglones del presupuesto
const cargandoPresupuesto = ref(false)
const presupuestoSeleccionado = ref(null)
const modalDescripcionAbierto = ref(false)
const modalRenglonAbierto = ref(false)
const renglonSeleccionado = ref(null)
const indiceRenglonActual = ref(null)
```

**Form structure:**
```javascript
const form = useForm({
  presupuesto_id: '',
  proveedor_id: '',
  fecha_oferta: new Date().toISOString().split('T')[0],
  plazo_entrega: '',
  forma_pago: '',
  validez_oferta: '30',
  observaciones: '',
  detalles: []  // Se llena solo con renglones seleccionados
})
```

**Función principal - cargarPresupuesto() (líneas 534-576):**
```javascript
const cargarPresupuesto = async () => {
  // Llamada a la API
  const response = await axios.get(route('presupuestos.api.detalles', form.presupuesto_id))
  const presupuesto = response.data

  // Mapeo de detalles a estructura de renglones
  renglones.value = presupuesto.detalles.map(detalle => ({
    renglon: detalle.renglon,
    insumo_id: detalle.insumo_id,
    insumo_codigo: detalle.insumo?.codigo || '',
    insumo_nombre: detalle.insumo?.descripcion || '',
    cantidad_solicitada: detalle.cantidad,
    precio_presupuesto: detalle.precio_unitario || 0,
    especificaciones_tecnicas: detalle.especificaciones_tecnicas || '',
    observaciones_presupuesto: detalle.observaciones || '',
    // Datos editables de la oferta
    ofertado: false,  // Checkbox
    cantidad_ofertada: '',
    precio_ofertado: '',
    marca: '',
    modelo: '',
    caracteristicas_tecnicas: '',
    garantia: '',
    tiempo_entrega: '',
    observaciones: '',
    subtotal: 0
  }))
}
```

**Computed properties:**
- `totalOferta`: Suma de subtotales de renglones ofertados
- `renglonesOfertados`: Cuenta cuántos renglones están seleccionados
- `todosMarcados`: Para el checkbox "Seleccionar todos"

**Funciones auxiliares:**
- `toggleTodos()`: Marcar/desmarcar todos los renglones
- `toggleRenglon(index)`: Limpiar valores al desmarcar
- `calcularSubtotal(index)`: Cantidad × Precio
- `abrirModalDescripcion(index)`: Abrir modal de oferta
- `verDescripcionRenglon(renglon)`: Abrir modal de presupuesto
- `formatearMoneda(monto)`: Formato argentino

**Función de guardado - guardarOferta() (líneas 675-708):**
```javascript
const guardarOferta = () => {
  // Filtrar SOLO los renglones seleccionados con cantidad y precio válidos
  const detallesOferta = renglones.value
    .filter(r => r.ofertado &&
                 parseFloat(r.cantidad_ofertada) > 0 &&
                 parseFloat(r.precio_ofertado) > 0)
    .map(r => ({
      renglon: r.renglon,
      insumo_id: r.insumo_id,
      cantidad: parseFloat(r.cantidad_ofertada),
      precio_unitario: parseFloat(r.precio_ofertado),
      marca: r.marca || null,
      modelo: r.modelo || null,
      caracteristicas_tecnicas: r.caracteristicas_tecnicas || null,
      garantia: r.garantia || null,
      tiempo_entrega: r.tiempo_entrega || null,
      observaciones: r.observaciones || null
    }))

  form.detalles = detallesOferta
  form.post(route('ofertas.store'))
}
```

### 5. Frontend: Otros archivos actualizados

#### Index.vue
- Actualizado para usar rutas `ofertas.*`
- Listado completo con filtros
- Estadísticas por estado

#### Show.vue
- Actualizado para usar rutas `ofertas.*`
- Vista detallada de oferta
- Solo permite editar si estado = 'pendiente'

#### Edit.vue
- Actualizado para usar rutas `ofertas.*`
- Similar a Create pero con datos precargados
- Presupuesto no editable (disabled)

---

## Características Implementadas

### ✅ Selección Flexible de Renglones
- El proveedor puede ofertar **algunos o todos** los renglones
- Sistema de checkboxes individual por renglón
- Opción "Seleccionar todos" en el header

### ✅ Información del Presupuesto
- Al seleccionar presupuesto, se cargan automáticamente todos sus renglones
- Se muestra cantidad solicitada y precio presupuestado
- Link para ver descripción ampliada de cada renglón

### ✅ Datos de la Oferta
- Cantidad ofertada (puede diferir de la solicitada)
- Precio ofertado
- Marca y modelo del producto ofertado
- Características técnicas detalladas
- Garantía
- Tiempo de entrega específico
- Observaciones por renglón

### ✅ Validaciones
- Solo se envían renglones con checkbox activo
- Requiere cantidad y precio válidos
- Validaciones del lado del servidor en OfertaController
- Verificación de permisos y estado del presupuesto

### ✅ Cálculos Automáticos
- Subtotal por renglón: cantidad × precio
- Total general de la oferta
- Actualización en tiempo real

### ✅ UX/UI
- Dark mode compatible
- Modales informativos
- Inputs deshabilitados cuando no aplican
- Formato de moneda argentino
- Mensajes claros de estado

---

## Flujo de Trabajo

```
1. Usuario accede a /ofertas/create
2. Selecciona un presupuesto aprobado
3. Sistema carga todos los renglones vía API
4. Usuario marca checkboxes de renglones a ofertar
5. Usuario completa:
   - Cantidad ofertada
   - Precio ofertado
   - (Opcional) Detalles adicionales vía modal
6. Sistema calcula subtotales automáticamente
7. Usuario completa información general:
   - Proveedor
   - Fecha oferta
   - Condiciones comerciales
8. Usuario envía formulario
9. Backend valida y guarda oferta
10. Redirección a la vista de detalles
```

---

## Bugs Corregidos

### Bug #1: Error 500 al cargar presupuesto
**Problema:** El método `obtenerDetalles()` intentaba cargar la relación `proveedor` que no existe en el modelo Presupuesto.

**Error:**
```
Call to undefined relationship [proveedor] on model [App\Models\Presupuesto]
```

**Solución:** Eliminada la línea que cargaba `'proveedor'` del array de relaciones en PresupuestoController.php línea 993.

**Explicación:** Los presupuestos no tienen proveedores. Las **ofertas** son las que están asociadas a proveedores específicos.

---

## Archivos Modificados

```
✓ routes/web.php
✓ app/Http/Controllers/PresupuestoController.php
✓ app/Http/Controllers/OfertaController.php
✓ resources/js/Pages/Ofertas/Create.vue
✓ resources/js/Pages/Ofertas/Index.vue
✓ resources/js/Pages/Ofertas/Show.vue
✓ resources/js/Pages/Ofertas/Edit.vue
```

---

## Compilación

```bash
npm run build
```

**Status:** ✅ Compilación exitosa
- Client bundle: 244.00 kB (86.73 kB gzipped)
- SSR bundle: Create-BZHgfp0S.js - 81.97 kB
- Sin errores de compilación

---

## Testing Recomendado

1. **Crear oferta completa:**
   - Seleccionar presupuesto
   - Verificar carga de renglones
   - Marcar algunos renglones
   - Completar cantidades y precios
   - Agregar descripciones vía modal
   - Guardar oferta

2. **Casos edge:**
   - Intentar guardar sin seleccionar renglones
   - Intentar guardar con precio en 0
   - Verificar que solo se guardan renglones seleccionados
   - Probar "Seleccionar todos" / "Deseleccionar todos"

3. **Permisos:**
   - Verificar acceso según roles (operador, administrador, etc.)
   - Verificar que solo aparecen presupuestos aprobados

4. **Responsividad:**
   - Probar en distintos tamaños de pantalla
   - Verificar scroll horizontal en la tabla
   - Probar dark mode

---

## Próximos Pasos Sugeridos

1. **Comparación de ofertas:** Pantalla para comparar múltiples ofertas de un mismo presupuesto
2. **Evaluación de ofertas:** Sistema de puntuación y ranking
3. **Adjudicación:** Seleccionar ofertas ganadoras
4. **Generación de órdenes de compra:** Desde ofertas adjudicadas
5. **Exportación a PDF:** Imprimir ofertas
6. **Notificaciones:** Avisar a proveedores del estado de sus ofertas
7. **Historial:** Tracking de cambios de estado

---

## Notas Técnicas

- **API Endpoint:** `/presupuestos/api/{presupuesto}/detalles` devuelve JSON puro (no Inertia)
- **Ziggy routes:** Todas las rutas disponibles en JavaScript vía `route()` helper
- **Axios:** Usado para llamadas API asíncronas
- **Inertia forms:** `useForm()` para manejo de formularios con validación
- **Vue 3 Composition API:** `ref()`, `computed()`, `watch()`
- **Tailwind CSS:** Clases utility-first con soporte dark mode

---

## Documentación de Referencia

- Modelo Presupuesto: `app/Models/Presupuesto.php`
- Modelo Oferta: `app/Models/Oferta.php`
- Modelo DetalleOferta: `app/Models/DetalleOferta.php`
- Validaciones: `app/Http/Controllers/OfertaController.php` líneas 129-148

---

**Fin del documento**
