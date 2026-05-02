package com.issue.tracker.users;

import java.util.List;
import java.util.Optional;

public interface UserInterface {
	
	public List<Users>GetAll();
	public Optional<Users>GetId(int id);
	public int Save(Users i);
	public void Delete(int id);
	public Optional<Users> GetByUsername(String username);

}
