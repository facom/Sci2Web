################################################################################
# MercuPy Wrap
# Jorge Zuluaga (C) 2011
################################################################################
FC=gfortran
PROGRAMS=mercury6_2.exe element6.exe close6.exe

#################################################################################
#BASIC RULS
#################################################################################
all:$(PROGRAMS)

%.exe:%.for
	$(FC) $^ -o $@ &>> errors.log

utilbuild:
	echo "Compiling util programs..."
	make -s -C util all install

#################################################################################
#MERCUPY PIPELINE
#################################################################################
prepare:
	bin/mercupy-prepare

build:
	make all

continue:
	bin/mercupy-continue

run:$(PROGRAMS)
	time ./mercury6_2.exe
	./element6.sh
	./close6.sh
#THIS LINE WAS ADDED FOR THE RUN PIPELINE OF SCI2WEB
	date +%s.%N > end.sig

out:
	bin/mercupy-output

ref:
	bin/mercupy-ref2ref

plot:
	bin/mercupy-plot

go:
	make prepare build run out ref plot

#################################################################################
#CLEAN RULES
#################################################################################
clean:cleanprepare

cleanprepare:cleanbuild
	rm -rf  *.in *.inc

cleanbuild:cleanrun
	rm -rf *.exe bin/*.pyc

cleanrun:cleanout
	rm -rf *.out *.dmp *.tmp
	rm -rf *.state
#ADDED FOR THE RUN PIPELINE OF SCI2WEB
	rm -rf *.sig
	rm -rf run.sh run.conf submit.sh

cleanout:cleanref
	rm -rf output/*

cleanref:cleanplot
	rm -rf output/*.ref

cleanplot:cleantmp
	rm -rf output/*.png

cleantmp:
	rm -rf *~ *.tmp *.dmp *.log tmp/*
