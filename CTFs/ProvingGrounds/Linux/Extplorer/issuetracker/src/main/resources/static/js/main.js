function confirmDelete(urlbase,id) {
	swal({
		  title: "Are you sure?",
		  text: "Once deleted, you will not be able to recover this imaginary file!",
		  icon: "warning",
		  buttons: true,
		  dangerMode: true,
		})
		.then((OK) => {
		  if (OK) {
			  $.ajax({
				  url:urlbase+'/delete/'+id,
				  success: function(res) { }
			  })
		    swal("Poof! Record deleted!", {
		      icon: "success",
		    }).then((ok)=>{
		    	if(ok){
		    		location.href=urlbase+"/list";
		    	}
		    });
		  } else {
		    swal("Your imaginary file is safe!");
		  }
		});
}