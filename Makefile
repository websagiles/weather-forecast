# =============================================================================
# Makefile para Symfony 7.2 y PHP 8.4
# =============================================================================
# Requisitos:
#   - Composer
#   - Symfony CLI
#   - PHP 8.4
#   - PowerShell
#
# Uso: make [comando]

# Colores para mensajes
CYAN := \033[36m
GREEN := \033[32m
RESET := \033[0m

# Mostrar ayuda
help:
	@echo -----------------------------------------------------------------
	@echo $(CYAN)Comandos disponibles:$(RESET)
	@echo -----------------------------------------------------------------
	@echo $(GREEN)Gestión de dependencias:$(RESET)
	@echo   install     → Instalar dependencias del proyecto
	@echo   update      → Actualizar dependencias
	@echo -----------------------------------------------------------------
	@echo $(GREEN)Servidor de desarrollo:$(RESET)
	@echo   serve       → Iniciar servidor de desarrollo
	@echo   stop        → Detener servidor de desarrollo
	@echo   open        → Abrir proyecto en el navegador
	@echo -----------------------------------------------------------------
	@echo $(GREEN)Base de datos:$(RESET)
	@echo   db         → Crear/recrear base de datos
	@echo   db-test    → Crear base de datos de pruebas
	@echo   fixtures   → Cargar datos de prueba
	@echo -----------------------------------------------------------------
	@echo $(GREEN)Assets y compilación:$(RESET)
	@echo   asset      → Compilar Tailwind y assets
	@echo   build      → Compilar AssetMapper
	@echo -----------------------------------------------------------------
	@echo $(GREEN)Testing y calidad:$(RESET)
	@echo   test       → Ejecutar tests unitarios
	@echo   behat      → Ejecutar tests behat
	@echo   coverage   → Generar reporte de cobertura
	@echo   phpstan    → Análisis estático de código
	@echo   validate   → Ejecutar todas las validaciones
	@echo   cypress    → Abrir Cypress para E2E tests
	@echo -----------------------------------------------------------------
	@echo $(GREEN)Utilidades:$(RESET)
	@echo   entity     → Crear nueva entidad
	@echo   clean      → Limpiar caché y logs
	@echo   fix-build  → Recrear DB + fixtures + build
	@echo -----------------------------------------------------------------

.PHONY: help install update serve stop open test coverage phpstan validate cypress entity clean db db-test fixtures asset build fix-build

# ==== Gestión de dependencias ====
install: ## Instalar dependencias del proyecto
	@echo $(CYAN)Instalando dependencias...$(RESET)
	symfony composer install
	symfony composer dump-autoload
	@echo $(GREEN)✓ Dependencias instaladas correctamente$(RESET)

update: ## Actualizar dependencias
	@echo $(CYAN)Actualizando dependencias...$(RESET)
	symfony composer update
	symfony composer dump-autoload
	@echo $(GREEN)✓ Dependencias actualizadas correctamente$(RESET)

# ==== Servidor de desarrollo ====
serve: ## Iniciar servidor de desarrollo
	@echo $(CYAN)Iniciando servidor de desarrollo...$(RESET)
	symfony server:start
	@echo $(GREEN)✓ Servidor iniciado en http://localhost:8000$(RESET)

stop: ## Detener servidor de desarrollo
	@echo $(CYAN)Deteniendo servidor...$(RESET)
	symfony server:stop
	@echo $(GREEN)✓ Servidor detenido$(RESET)

open: ## Abrir proyecto en navegador
	symfony open:local

# ==== Testing y calidad ====
test: ## Ejecutar tests unitarios
	@echo $(CYAN)Ejecutando tests...$(RESET)
	php bin/phpunit
	@echo $(GREEN)✓ Tests completados$(RESET)

behat: ## Ejecutar tests unitarios
	@echo $(CYAN)Ejecutando tests behat...$(RESET)
	php vendor/bin/behat
	@echo $(GREEN)✓ Tests completados$(RESET)


cypress: ## Ejecutar tests E2E con Cypress
	@echo $(CYAN)Iniciando Cypress...$(RESET)
	bunx cypress open

# ==== Base de datos ====
db: ## Crear/recrear base de datos
	@echo $(CYAN)Recreando base de datos...$(RESET)
	symfony console doctrine:database:drop --force
	symfony console doctrine:database:create
	symfony console doctrine:schema:create
	@echo $(GREEN)✓ Base de datos creada correctamente$(RESET)

db-test: ## Crear base de datos de pruebas
	@echo $(CYAN)Recreando base de datos de pruebas...$(RESET)
	symfony console doctrine:database:drop --force -e test
	symfony console doctrine:database:create -e test
	symfony console doctrine:schema:create -e test
	@echo $(GREEN)✓ Base de datos de pruebas creada correctamente$(RESET)

fixtures: ## Cargar datos de prueba
	@echo $(CYAN)Cargando fixtures...$(RESET)
	symfony console doctrine:fixtures:load -n
	@echo $(GREEN)✓ Fixtures cargados correctamente$(RESET)

# ==== Assets y compilación ====
asset: ## Compilar Tailwind y assets
	@echo $(CYAN)Compilando Tailwind y assets...$(RESET)
	symfony console tailwind:build
	symfony console asset-map:compile
	@echo $(GREEN)✓ Assets compilados correctamente$(RESET)

build: ## Compilar AssetMapper
	@echo $(CYAN)Compilando AssetMapper...$(RESET)
	symfony console assets:install
	@echo $(GREEN)✓ AssetMapper compilado correctamente$(RESET)

# ==== Testing y calidad ====
phpstan: ## Análisis estático de código
	@echo $(CYAN)Ejecutando PHPStan...$(RESET)
	vendor/bin/phpstan analyse src tests --no-progress --error-format=raw
	@echo $(GREEN)✓ Análisis estático completado$(RESET)

coverage: ## Generar reporte de cobertura
	@echo $(CYAN)Generando reporte de cobertura...$(RESET)
	php -d xdebug.mode=coverage $(PHPUNIT) --coverage-html var/coverage
	@echo $(GREEN)✓ Reporte generado en var/coverage/index.html$(RESET)

validate: phpstan test ## Ejecutar todas las validaciones
	@echo $(GREEN)✓ Todas las validaciones completadas$(RESET)

# ==== Utilidades ====
entity: ## Crear nueva entidad
	@echo $(CYAN)Creando nueva entidad...$(RESET)
	symfony console make:entity

clean: ## Limpiar caché y logs
	@echo $(CYAN)Limpiando caché y logs...$(RESET)
	rm -rf var/cache/* var/log/*
	@echo $(GREEN)✓ Caché y logs limpiados correctamente$(RESET)

fix-build: db fixtures build ## Recrear DB + fixtures + build
	@echo $(GREEN)✓ Entorno reiniciado correctamente$(RESET)
