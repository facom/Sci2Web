#include <diffusion.h>
#include <string.h>

main(int argc,char *argv[])
{
  int ini=1;
  double ndisp=0;
  double x,y,lambda,h,l,d,theta;
  int i,j,N,condition;
  int per=100;

  time_t *t;
  FILE *fl,*fp;
  char fname[100];
  char mode[]="w";
  srand48(time(NULL));

  fl=fopen("parameters.ini","r");
  fscanf(fl,"%d",&N);
  fscanf(fl,"%lf",&lambda);
  fscanf(fl,"%lf %lf",&h,&l);
  fclose(fl);

  //////////////////////////////////////////
  //RESUME
  //////////////////////////////////////////
  fl=fopen(argv[1],mode);
  for(i=ini;i<=N;i++){
    condition=((i%per)==0 || i==ini);
    x=l*drand48();
    y=h*drand48();
    if(condition){
      sprintf(fname,"scratch/path-%d.dat",i);
      fp=fopen(fname,"w");
    }
    j=0;
    do{
      d=-lambda*log(drand48())*CUSTOM_UNITS;
      theta=2*M_PI*drand48();
      x=x+d*cos(theta);
      y=y+d*sin(theta);
      if(condition) fprintf(fp,"%d %e %e\n",j,x,y);
      j++;
    }while(x*(x-l)<=0 && y*(y-h)<=0);
    ndisp+=j;

    fprintf(fl,"%d\t%e\t%e\n",i,x,y);
    if(condition){
      fprintf(stdout,"i = %d, (x,y) = (%.2e,%.2e)\n",i,x,y);
      sleep((int)SLEEP);
    }
  }
  fclose(fl);
}
