<template>
<!-- <link href="https://fonts.googleapis.com/css?family=Lora" rel="stylesheet"> -->
<div id="app" class="body">
  <v-app id="inspire">
    <v-container grid-list-md text-xs-center>
      <v-layout row wrap>
      <v-flex xs5>
          <v-card>
            <v-img id="product-img"
              src="https://cdn.shopify.com/s/files/1/0054/3214/8081/products/45_665ca830-d1f2-48bd-8c84-02375dc2d883_large.jpg?v=1544128468"
              height="100%"
              class="grey darken-4"
            ></v-img>
          </v-card>
        <v-layout row wrap>
      <v-flex v-for="item in items" :key="`4${i}`" xs3>
        <v-card class="product-img">
        <v-img class="sub-img" v-bind:class="{activeimg:  item.isActive}"
          :src="item.images"
           
        ></v-img>
        </v-card>
      </v-flex>
      </v-layout>
      </v-flex>
      <v-flex xs7  pl-5 class="d-flex text-xs-left">
        <div class="product-summary">
          <h2 class="product-title">Basic contrast sneakers</h2>
          <div class="user-ratings">
            <div class="star-rating">
              <span style="width:100%"></span>
            </div>
         <a href="#reviews" class="total-review" rel="nofollow">(<span class="count"> 5 </span> customer review)</a>
          </div>
          <div class="price">
            <h3>
            <span>$</span>
            160.00
            </h3>
            <del>$ 180</del>
          </div>
          <div class="prodect-details">
            <h3>Product Details</h3>
            <p>Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam eaque ipsa, quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt, explicabo. Nemo enim ipsam voluptatem,</p>
          </div>
          <div>
            <ul class="colors" id="col-box">
              <p>Select Colour : </p>
              <li class="color-box yellow active-col"></li>
              <li class="color-box pink"></li>
              <li class="color-box cyan "></li>
            </ul>
          </div>
          <div>
             <ul class="size" id="size-box">
              <p class="sub-heading">Select Size : </p>
              <li class="size-box active-siz">M</li>
              <li class="size-box">L</li>
              <li class="size-box">XL</li>
            </ul>
          </div>
          <div class="buy-product">
            <div class="stock">
             <v-btn color="#4CAF50" dark>IN STOCKS
              <v-icon dark right>check_circle</v-icon>
             </v-btn>
            </div>
            <div class="product-qty">
              <table>
                <tr>
                  <td><label class="sub-title">Quantity :</label></td>
                  <td><button onClick="var result = document.getElementById('qty'); var qty = result.value; if( !isNaN( qty ) &amp;&amp; qty &gt; 0 ) result.value--;return false;" class="reduced items-count" type="button"><i class="fa fa-angle-left" aria-hidden="true"></i></button>
                        <input type="text" class="input-text qty" title="Qty" value="1" maxlength="12" id="qty" name="qty">
                        <button onClick="var result = document.getElementById('qty'); var qty = result.value; if( !isNaN( qty )) result.value++;return false;" class="increase items-count" type="button"><i class="fa fa-angle-right" aria-hidden="true"></i></button>
                </tr>
              </table>
              <div class="cart-btn">
              <v-btn  class="px-5" color="error"><i class="fa fa-shopping-cart" aria-hidden="true"></i> ADD TO CART</v-btn>
                            <v-btn  class="cart-btn px-5" color="error">BUY NOW</v-btn>
              </div>
              <button><i class="fa fa-heart" aria-hidden="true"></i>    ADD TO WISHLIST</button>
              <ul class="socal-link">
                <li><a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
                <li><a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                <li><a href="#"><i class="fa fa-linkedin" aria-hidden="true"></i></a></li>
                <li><a href="#"><i class="fa fa-rss"></i></a></li>
                <li><a href="#"><i class="fa fa-youtube"></i></a></li>
                <li><a href="#"><i class="fa fa-instagram"></i></a></li>
              </ul>
            </div>
          </div>
        </div>
      </v-flex>
       
   <v-flex xs12>
          <div class="overview">
    <v-tabs
      v-model="active"
      color="#ff5252"
      dark
      slider-color="blue"
    >
      <v-tab
        v-for="description in overview"
        :key="n"
        ripple
      >
        {{ description.menu }}

      </v-tab>
      <v-tab-item
        v-for="description in overview"
        :key="n"
      >
        <v-card flat>
          <v-card-text class="text-sm-left">{{ description.details }}</v-card-text>
        </v-card>
      </v-tab-item>
    </v-tabs>
  </div>
      </v-flex>
        
      </v-layout>
    </v-container>
  </v-app>
</div>
</template>
<script>
    
new Vue({
  el: '#app',
  data: {
    default: 'https://cdn.shopify.com/s/files/1/0054/3214/8081/products/45_665ca830-d1f2-48bd-8c84-02375dc2d883_large.jpg?v=1544128468',
    items: [
      { images: 'https://cdn.shopify.com/s/files/1/0054/3214/8081/products/45_665ca830-d1f2-48bd-8c84-02375dc2d883_large.jpg?v=1544128468',
      isActive: true
      },
      { images: 'https://cdn.shopify.com/s/files/1/0054/3214/8081/products/47_large.jpg?v=1544128468' },
      { images: 'https://cdn.shopify.com/s/files/1/0054/3214/8081/products/35_8364d9f9-d670-418a-acad-ce4832921345_large.jpg?v=1544128468' },
      {
      images: 'https://cdn.shopify.com/s/files/1/0054/3214/8081/products/30_c7961d18-9fcb-4148-bfae-ff3063a99a82_large.jpg?v=1544128468'
      }
    ],
    overview:[
      {
        menu: 'DESCRIPTION',
        details: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.'
      },
            {
        menu: 'REVIEWS',
        details: 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using Content here, content here, making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for lorem ipsum will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like'
      },
            {
        menu: 'TAG',
        details: 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.'
      },
            {
        menu: 'CUSTOM TAB',
        details: 'There are many products of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn\'t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.'
      }
    ]
  }
})



var header = document.getElementById("col-box");
var btns = header.getElementsByClassName("color-box");
for (var i = 0; i < btns.length; i++) {
  btns[i].addEventListener("click", function() {
  var current = document.getElementsByClassName("active-col");
  current[0].className = current[0].className.replace(" active-col", "");
  this.className += " active-col";
  });
}


var header = document.getElementById("size-box");
var btns = header.getElementsByClassName("size-box");
for (var i = 0; i < btns.length; i++) {
  btns[i].addEventListener("click", function() {
  var current = document.getElementsByClassName("active-siz");
  current[0].className = current[0].className.replace(" active-siz", "");
  this.className += " active-siz";
  });
}



var subImg = document.getElementsByClassName("sub-img");
for (var i = 0; i < subImg.length; i++) {
  subImg[i].addEventListener("click", function() {
  var currentClass = document.getElementsByClassName("activeimg");
    var currentImg = document.getElementsByClassName("activeimg");
  currentClass[0].className = currentClass[0].className.replace("activeimg", "");
  this.className += " activeimg";
  document.getElementById("product-img").src = currentImg.src;
  });
}
</script>
<style>

.body{
  padding: 35px 0px;
}
*{
  box-sizing: border-box;
 font-family: 'Lora', serif;
 
}
.product-img{
  cursor: pointer;
  margin-top: 10px;
}

.product-title{
      text-transform: uppercase;
    margin-bottom: 15px;
    font-size: 25px;
    line-height: 1.2em;
    font-family: 'Lora', serif;
}
.user-ratings{
      overflow: hidden;
    margin-bottom: 10px;
    margin-top: 5px;
    display: inline-block;
}

.star-rating{
  display: inline-block;
    font-size: 18px;
    position: relative;
    height: 18px;
    line-height: 18px;
    letter-spacing: 2px;
    width: 130px;
    font-family: FontAwesome;
}

.star-rating:before{
content: "\f005\f005\f005\f005\f123";
    position: absolute;
    top: 0;
    left: 0;
    color: #FC0;
  box-sizing: border-box;
}

.star-rating span{
      overflow: hidden;
    float: left;
    top: 0;
    left: 0;
    height: 18px;
    position: absolute;
    font-size:0;
}
.user-ratings .total-review{
  vertical-align: middle;
  color: rgba(0,0,0,.5);
  box-sizing: border-box;
  float: right;
  font-size: 14px;
  text-decoration: none;
}
.total-review:hover{
  text-decoration: underline;
}

.price{
  display: block;
  margin-bottom: 10px;
}
.price h3{
  display: inline-block;
  font-size: 28px;
  color: #E91E63;
}
.price del{
  padding: 0px 20px;
  font-size: 20px;
  color:#9E9E9E;
}
.prodect-details{
  padding-top: 15px;
    padding-bottom: 20px;
font-family: 'Lora', serif;
  border-bottom: 1px dashed #ddd;
}

.colors{
  margin: 0px;
  padding: 0px;
  display: inline-block;
  margin-top: 20px;
    list-style: none;
}
.colors p{
  float: left;
  margin-top: 10px;
  color: #000;
  font-weight: 700;
  padding-right: 20px;
}
.color-box{
  height:35px;
  width: 35px;
  margin:5px;
  display: inline-block;
  cursor: pointer;
  opacity: 0.8
}

.active-col{
    outline: 1px dashed black;
}

.size{
    margin: 0px;
  padding: 0px;
  display: inline-block;
  margin-top: 20px;
    list-style: none;
}
.size p{
  float: left;
  margin-top: 10px;
  color: #000;
  font-weight: 700;
  padding-right: 38px;
}

.size-box{
  padding: 12px;
  margin: 0px 5px;
  display: inline-block;
  cursor: pointer;
  color: #000;
  font-weight: 400;
  
}
.active-siz{
  background-color: #B2DFDB;
}
.buy-product{
  margin-top: 15px;
  padding: 15px 0px;
  border-top: 1px dashed #ddd;
  
}

.product-qty{
  display: inline-block;
  margin-top: 20px;
  width: 100%;
}

.product-qty button.items-count {
    background-color: #fff;
    border: 1px #ddd solid;
    border-radius: 2px;
}

.product-qty button.items-count {
    font-size: 18px;
    line-height: 12px;
    height: 40px;
    width: 40px;
   color:#000;
}

.product-qty .qty {
    height: 40px;
    text-align: center;
    width: 70px;
    vertical-align: top;
  color: #000;
}

.qty {
    background-color: #ffffff;
    border: 1px #ced4da solid;
    border-radius: 2px;
    color: #666;
    font-size: 15px;
    font-weight: bold;
    margin: 0 -5px;
}

.sub-title{
  color: #000;
  font-weight: 700;
  padding-right: 38px;
}

.cart-btn{
  margin: 15px 0px;
  border-bottom: 1px #e5e5e5 solid;
}

.cart-btn i{
  padding:0px 10px;
}

.socal-link{
  margin: 0px;
  padding: 0px;
  margin-top: 20px;
  list-style: none;
  display: block;
}
.socal-link li{
    list-style: none;
  display: inline-block;
  margin: 0px 8px;
}

.socal-link li a{
      border: 1px #ddd solid;
    color: #666;
    font-size: 15px;
    line-height: 36px;
    padding: 8px 12px;
}

.socal-link li a:hover{
  background: #ff5252;
  color: #fff;
}

.activeimg{
  border: 1px solid #000;
}

.overview{
  margin-top: 30px;
}

</style>

<template>
  <div class="container">
      <v-card class="mx-auto" elevation="8" rounded="lg">
          <v-card-title class="text-center">
              <h2>{{ $route.name == 'product.create' ? "Product Create" : 'Product Edit' }}</h2>
          </v-card-title>

          <v-divider></v-divider>
          <v-card-text class="" style="max-height: 500px; overflow-y: auto;">
              <v-row>
                  <v-col cols="6" class="text-center">
                      <v-img :src="image_url || '/images/profile_image.png'" max-width="100" class="mb-4"
                          rounded="lg">
                      </v-img>
                  </v-col>

                  <!-- Centered Buttons for Choose and Remove Image -->
                  <v-col cols="6" class="text-center">
                      <div class="d-flex justify-center flex-column">
                          <v-btn color="blue" @click="chooseImage" class="mb-2">
                              Choose Image
                          </v-btn>
                          <v-btn color="red" @click="removeImage" v-if="form.image">
                              Remove Image
                          </v-btn>
                      </div>
                      <input ref="imageInput" type="file" accept="image/*" class="d-none" @change="onImageSelected" />
                  </v-col>
              </v-row>
              <v-row>
                  <!-- Name Field -->
                  <v-col cols="12">
                      <v-text-field label="Name" variant="outlined" v-model="form.name" density="compact"
                          :error-messages="v$.form.name.$errors.map(e => e.$message)" @blur="v$.form.name.$touch()"
                          :rules="[(v) => !!v || 'Name is required']">
                      </v-text-field>
                  </v-col>
                  <!-- category_id, sub_category_id Fields -->
                  <v-col cols="12">
                      <v-autocomplete v-model="form.category_id" :items="category_list" label="Category"
                          variant="outlined" density="compact" item-title="name" item-value="id"
                          :error-messages="v$.form.category_id.$errors.map(e => e.$message)"
                          @blur="v$.form.category_id.$touch()">
                      </v-autocomplete>

                  </v-col>
                  <v-col cols="6">
                      <v-autocomplete v-model="form.sub_category_id" :items="sub_category_list" label="Sub Category"
                          variant="outlined" density="compact" item-title="name" item-value="id"
                          :error-messages="v$.form.sub_category_id.$errors.map(e => e.$message)"
                          @blur="v$.form.sub_category_id.$touch()">
                      </v-autocomplete>
                  </v-col>

                  <!-- Description Field -->
                  <v-col cols="12">
                      <v-textarea label="Description" variant="outlined" v-model="form.description" hide-details
                          auto-grow rows="2" density="compact">
                      </v-textarea>
                  </v-col>
              </v-row>
          </v-card-text>

          <!-- Divider -->
          <v-divider class="my-4"></v-divider>

          <!-- Actions Section -->
          <v-card-actions class="d-flex justify-center">
              <v-btn class="mx-2" :loading="loader" :disabled="loader" @click="submitForm()">
                  {{ $route.name == 'product.create' ? 'Save' : 'Update' }}
              </v-btn>
              <v-btn color="error" class="mx-2" @click="close">Close
              </v-btn>
          </v-card-actions>
      </v-card>
  </div>
</template>