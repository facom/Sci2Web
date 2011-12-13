name="salida"
set term png
set output name.'.png'
plot name.'.out' u 2:3 w p
