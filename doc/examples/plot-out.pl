name="salida"
set term png
set output name.'.png'
plot name.'.txt' u 2:3 w p
