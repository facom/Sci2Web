name="salida"
set title "Diffusion of 10000 particles"
set term png
set output name.'.png'
plot name.'.txt' u 2:3 w p
