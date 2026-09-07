version = 3.2.3
release = $(version)-2
patch = 20678

all: build push

build:
	docker build --pull \
		--build-arg release=$(release) \
		--build-arg patch=$(patch) \
		-t supervisions/itop \
		-t supervisions/itop:3.2 \
		-t supervisions/itop:$(version) \
		-t supervisions/itop:$(release) .

push:
	docker push supervisions/itop
	docker push supervisions/itop:3.2
	docker push supervisions/itop:$(version)
