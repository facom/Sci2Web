################################################################################
# MercuPy Wrap
# Jorge Zuluaga (C) 2011
################################################################################
FC=gfortran
PROGRAMS=mercury6_2.exe element6.exe close6.exe

build:
	bin/mercupy-build

%.exe:%.for
	$(FC) $^ -o $@ 2> errors.log

all:$(PROGRAMS)

clean:
	rm -rf *.aei *.dat *.dat.* *.clo *.out *.dmp *.xyz *.els *.ref *.png
	find . -name "*~" -exec rm -rf {} \;

cleanfiles:
	rm -rf *.tmp *.exe *~ *.in *.log *.txt *.inc
	rm -rf tmp/*

cleanutil:
	make -C util clean

cleanall:clean cleanfiles cleanutil

utilbuild:
	echo "Compiling util programs..."
	make -C util all install

run:$(PROGRAMS)
	time ./mercury6_2.exe
	./element6.sh
	./close6.exe
#THIS LINE WAS ADDED FOR THE RUN PIPELINE OF SCI2WEB
	date +%s.%N > end.sig

plain:
	bin/mercupy-plain

plot:plain
	bin/mercupy-plot
