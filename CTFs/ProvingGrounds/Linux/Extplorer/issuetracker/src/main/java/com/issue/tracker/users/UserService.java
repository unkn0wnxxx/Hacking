package com.issue.tracker.users;

import java.util.List;
import java.util.Optional;


import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

@Service
public class UserService implements UserInterface {

	@Autowired
	private UsersRepository data;
	
	@Override
	public List<Users> GetAll() {
		return (List<Users>)data.findAll();
	}

	@Override
	public Optional<Users> GetId(int id) {
		return data.findById(id);
	}

	@Override
	public int Save(Users i) {
		int res=0;
		Users user = data.save(i);
		if (!user.equals(null)) {
			res=1;
		}
		
		return res;
	}

	@Override
	public void Delete(int id) {
		data.deleteById(id);
	}

	@Override
	public Optional<Users> GetByUsername(String username) {
		// TODO Auto-generated method stub
		return null;
	}


}
