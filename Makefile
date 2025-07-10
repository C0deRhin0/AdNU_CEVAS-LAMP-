n ?= 3

up:
	@docker compose up -d --scale app=$(n) proxy db

upb:

	@docker compose up -d --build --scale app=$(n) proxy db
down:
	@docker compose down
du: down up
	@echo	
