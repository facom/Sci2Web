CC = gcc
CFLAGS = -g -I.
LFLAGS = -g -lm

diffusion.out:diffusion.o
	$(CC) $^ $(LFLAGS) -o $@

clean:
	rm -rf *.dat *.png *~ *.err

cleanall:clean
	rm -rf *.o *.out

