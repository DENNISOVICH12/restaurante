-- Esquema base para tu app

-- 1) MENU
CREATE TABLE IF NOT EXISTS public.menu_items (
  id                SERIAL PRIMARY KEY,
  nombre            TEXT NOT NULL,
  descripcion       TEXT,
  precio            NUMERIC(10,2) NOT NULL CHECK (precio >= 0),
  imagen            TEXT,
  categoria         TEXT NOT NULL,            -- p.ej. 'plato', 'bebida', 'postre'
  disponible        BOOLEAN NOT NULL DEFAULT TRUE,
  fecha_creacion    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_menu_items_categoria ON public.menu_items (categoria);
CREATE INDEX IF NOT EXISTS idx_menu_items_disponible ON public.menu_items (disponible);

-- 2) CLIENTES
CREATE TABLE IF NOT EXISTS public.clientes (
  id              SERIAL PRIMARY KEY,
  nombre          TEXT NOT NULL,
  telefono        TEXT,
  direccion       TEXT,
  fecha_registro  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- 3) PEDIDOS
CREATE TABLE IF NOT EXISTS public.pedidos (
  id          SERIAL PRIMARY KEY,
  id_cliente  INTEGER REFERENCES public.clientes(id) ON DELETE SET NULL,
  fecha       TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  estado      TEXT NOT NULL DEFAULT 'pendiente',     -- pendiente | en_proceso | listo | cancelado
  mesa        TEXT
);
CREATE INDEX IF NOT EXISTS idx_pedidos_estado ON public.pedidos (estado);

-- 4) DETALLE DE PEDIDO
CREATE TABLE IF NOT EXISTS public.detalle_pedido (
  id              SERIAL PRIMARY KEY,
  id_pedido       INTEGER NOT NULL REFERENCES public.pedidos(id) ON DELETE CASCADE,
  nombre_producto TEXT NOT NULL,
  precio          NUMERIC(10,2) NOT NULL CHECK (precio >= 0),
  cantidad        INTEGER NOT NULL CHECK (cantidad > 0),
  categoria       TEXT,
  descripcion     TEXT
);

-- 5) USUARIOS
CREATE TABLE IF NOT EXISTS public.usuarios (
  id        SERIAL PRIMARY KEY,
  nombre    TEXT NOT NULL,
  correo    TEXT UNIQUE NOT NULL,
  password  TEXT NOT NULL,
  rol       TEXT NOT NULL DEFAULT 'mesero',          -- admin | mesero | cocina
  activo    BOOLEAN NOT NULL DEFAULT TRUE
);

-- Trigger simple para actualizar fecha_actualizacion en menu_items
CREATE OR REPLACE FUNCTION set_fecha_actualizacion()
RETURNS TRIGGER AS $$
BEGIN
  NEW.fecha_actualizacion = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS tg_menu_items_update ON public.menu_items;
CREATE TRIGGER tg_menu_items_update
BEFORE UPDATE ON public.menu_items
FOR EACH ROW
EXECUTE PROCEDURE set_fecha_actualizacion();

-- Datos de ejemplo para que index.php no falle
INSERT INTO public.menu_items (nombre, descripcion, precio, imagen, categoria, disponible)
VALUES
('Hamburguesa Clásica', 'Carne 150g, queso, lechuga, tomate', 18000, 'hamburguesa.png', 'plato', TRUE),
('Papas Fritas', 'Porción mediana', 8000, 'papas.png', 'plato', TRUE),
('Alitas BBQ', '6 unidades', 16000, 'alitas.png', 'plato', TRUE),
('Pasta Alfredo', 'Fettuccine con salsa Alfredo', 20000, 'pasta.png', 'plato', TRUE)
ON CONFLICT DO NOTHING;
