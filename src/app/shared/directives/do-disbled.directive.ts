import { Directive, Input, ElementRef, AfterViewChecked } from '@angular/core';

@Directive({
  // tslint:disable-next-line:directive-selector
  selector: '[doDisbled]'
})
export class DoDisbledDirective implements AfterViewChecked{
  @Input() doDisbled: boolean;
  constructor(
    private elementRef: ElementRef
  ) { }

  ngAfterViewChecked() {
    if (this.doDisbled) {
      this.elementRef.nativeElement.disabled = true;
    } else {
      this.elementRef.nativeElement.disabled = false;
    }
  }

}
