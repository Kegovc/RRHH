import { environment } from './../../environments/environment';
import { ReporteService } from './reporte.service';
import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-reportes',
  templateUrl: './reportes.component.html',
  styleUrls: ['./reportes.component.scss']
})
export class ReportesComponent implements OnInit {
  public load = false;
  public data: any = {};
  public reportes: any[] = [];
  public carga =  false;


  constructor(
    private reporteService: ReporteService
  ) {
    this.load = true;
    reporteService.getReportes()
    .then((response: any) => {
      if (environment.debug) { console.log(response); }
      if (response.fun.access) {
        this.reportes = response.fun.ls;
        this.carga = true;
        this.load = false;
      }
    });
   }

  descarga(id) {
    this.reporteService.getReporte({id: id})
    .then((response: any) => {
      if (environment.debug) { console.log(response); }
      if (response.fun.access) {
        this.toExecute(response.fun);
      }
    });
  }

  toExecute(data) {
    this.reporteService.execute(data);
  }
  ngOnInit() {
  }

}
