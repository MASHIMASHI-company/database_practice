<!DOCTYPE html>
<html>

<body>
  <?php include 'header.php'; ?>

  <main class="home">
    <div class="user">
      <a href="#" id="openSignIn" class="btn">SIGN IN</a>
      <a href="#" id="openSignUp" class="btn">SIGN UP</a>
    </div>
    <img src="../image/zebla.png" alt="Zebla Image">
  </main>

  <?php include 'footer.php'; ?>

  <!-- Sign In モーダル -->
  <div id="modalSignIn" class="modal">
    <div class="modal-content">
      <span class="close" id="closeSignIn">&times;</span>
      <h2>Sign In</h2>
      <form id="formSignIn">
        <label for="signin-username">Username:</label>
        <input type="text" id="signin-username" name="username" required>

        <label for="signin-password">Password:</label>
        <input type="password" id="signin-password" name="password" required>

        <button type="submit">Sign In</button>
      </form>
    </div>
  </div>

  <!-- Sign Up モーダル -->
  <div id="modalSignUp" class="modal">
    <div class="modal-content">
      <span class="close" id="closeSignUp">&times;</span>
      <h2>Sign Up</h2>
      <form id="formSignUp">
        <label for="signup-username">Username:</label>
        <input type="text" id="signup-username" name="username" required>

        <label for="signup-email">Email:</label>
        <input type="email" id="signup-email" name="email" required>

        <label for="signup-password">Password:</label>
        <input type="password" id="signup-password" name="password" required>

        <label for="signup-password2">Confirm Password:</label>
        <input type="password" id="signup-password2" name="password2" required>

        <button type="submit">Sign Up</button>
      </form>
    </div>
  </div>

  <script src="../js/signin.js"></script>
</body>


</html>
